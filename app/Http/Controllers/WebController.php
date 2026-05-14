<?php

namespace App\Http\Controllers;

use App\Repositories\EndatixFormRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class WebController extends Controller
{
    public $auth, $formrepository, $accessToken, $refreshToken;

    public function __construct(EndatixFormRepository $formrepository)
    {
        $this->formrepository = $formrepository;
    }

    public function login()
    {
        $response = $this->formrepository->login(new Request(), "post", "/auth/login");
        $this->accessToken = $response['accessToken'];
        $this->refreshToken = $response['refreshToken'];
    }

    public function refreshUserToken()
    {
        $response = $this->formrepository->refreshToken(new Request(), "post", "/auth/refresh", $this->refreshToken);
        $this->accessToken = $response['accessToken'];
        $this->refreshToken = $response['refreshToken'];
    }

    public function getFormsList()
    {
        $response = $this->formrepository->endatixApi(new Request(), "get", "/forms", $this->accessToken);
        return $response;
    }

    public function getSubmissionList($id)
    {
        $response = $this->formrepository->endatixApi(new Request(), "get", "/forms/{$id}/submissions", $this->accessToken);
        return $response;
    }

    public function getSingleSubmissionApi($form_id, $submission_id)
    {
        $response = $this->formrepository->endatixApi(new Request(), "get", "/forms/{$form_id}/submissions/{$submission_id}", $this->accessToken);
        return $response;
    }

    public function index()
    {
        $forms = [];
        $this->login();
        $formsList = $this->getFormsList();
        if ($formsList->ok()) {
            $forms = $formsList->json();
        } elseif ($formsList->unauthorized()) {
            $this->refreshUserToken();
            $formsList = $this->getFormsList();
            if ($formsList->ok()) {
                $forms = $formsList->json();
            }
        }
        return view('endatix.index', compact('forms'));
    }

    public function show(Request $request, string $id)
    {
        $sid = hash_hmac('sha256', session()->getId(), config('app.key'));

        $signedUrl = URL::temporarySignedRoute(
            'endatix.embed.proxy',
            now()->addMinutes(10),
            ['form_id' => $id, 'sid' => $sid]
        );

        return view('endatix.show', [
            'id'        => $id,
            'signedUrl' => $signedUrl,
        ]);
    }

    public function embedProxy(Request $request, string $form_id)
    {
        // 1) Session-bound HMAC — the actual auth
        $expectedSid = hash_hmac('sha256', session()->getId(), config('app.key'));
        if (!hash_equals($expectedSid, (string) $request->query('sid'))) {
            Log::warning('endatix.proxy: sid mismatch', [
                'session_id' => substr(session()->getId(), 0, 8),
            ]);
            abort(403, 'Signature mismatch');
        }

        // 2) Sec-Fetch checks — soft (header may be absent in some browsers)
        $dest = $request->header('Sec-Fetch-Dest');
        $site = $request->header('Sec-Fetch-Site');
        if ($dest !== null && $dest !== 'iframe') {
            abort(403, 'Must be loaded in an iframe');
        }
        if ($site !== null && !in_array($site, ['same-origin', 'same-site', 'none'], true)) {
            abort(403, 'Cross-site not allowed');
        }   

        // 3) Validate config
        $base = rtrim((string) env('ENDATIX_EMBEDED_URL'), '/');
        if ($base === '') {
            Log::error('endatix.proxy: ENDATIX_EMBEDED_URL not set');
            abort(500, 'Endatix upstream not configured');
        }

        $parts = parse_url($base);
        if (!isset($parts['scheme'], $parts['host'])) {
            Log::error('endatix.proxy: malformed base URL', ['base' => $base]);
            abort(500, 'Endatix upstream URL is malformed');
        }

        $origin = $parts['scheme'] . '://' . $parts['host']
            . (isset($parts['port']) ? ':' . $parts['port'] : '');

        // 4) Fetch upstream
        try {
            $upstream = Http::timeout(15)
                ->withHeaders([
                    'Accept'     => 'text/html,application/xhtml+xml,*/*',
                    'User-Agent' => $request->userAgent() ?? 'Laravel-Endatix-Proxy/1.0',
                ])
                ->get($base . '/' . $form_id);
        } catch (\Throwable $e) {
            Log::error('endatix.proxy: upstream fetch failed', [
                'url'   => $base . '/' . $form_id,
                'error' => $e->getMessage(),
            ]);
            abort(502, 'Failed to reach Endatix');
        }

        $body        = (string) $upstream->body();
        $contentType = $upstream->header('Content-Type') ?: 'text/html; charset=utf-8';

        Log::info('endatix.proxy: upstream response', [
            'url'    => $base . '/' . $form_id,
            'status' => $upstream->status(),
            'length' => strlen($body),
        ]);

        if (!$upstream->successful()) {
            return response($body ?: 'Upstream returned ' . $upstream->status(), $upstream->status())
                ->header('Content-Type', $contentType);
        }

        // 5) Inject <base> so relative URLs (including in fetch/XHR) resolve to upstream
        if (stripos($contentType, 'text/html') !== false && $body !== '') {
            $baseTag = '<base href="' . htmlspecialchars($origin . '/', ENT_QUOTES) . '">';

            if (stripos($body, '<head') !== false) {
                $rewritten = preg_replace(
                    '/<head([^>]*)>/i',
                    '<head$1>' . $baseTag,
                    $body,
                    1
                );
                $body = $rewritten ?? $body;
            } else {
                $body = '<!doctype html><html><head>' . $baseTag . '</head>' . $body . '</html>';
            }
        }

        return response($body, $upstream->status() ?: 200)
            ->header('Content-Type', $contentType)
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function submission($id)
    {
        $this->login();
        $submissionList = $this->getSubmissionList($id);
        $submission = $submissionList->json();
        // return $submissionList;
        return view('endatix.submission', compact('submission'));
    }

    public function getSingleSubmission($form_id, $submission_id)
    {
        $this->login();
        $submission = $this->getSingleSubmissionApi($form_id, $submission_id);
        return $submission;
    }

    public function store(Request $request)
    {
        Log::info("Endatix Webhook Responses: " . json_encode($request->all()));
    }
}
