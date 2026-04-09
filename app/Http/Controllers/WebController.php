<?php

namespace App\Http\Controllers;

use App\Repositories\EndatixFormRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebController extends Controller
{
    public $auth,$formrepository,$accessToken,$refreshToken;

    public function __construct(EndatixFormRepository $formrepository)
    {
        $this->formrepository = $formrepository;
    }

    public function login()
    {
        $response = $this->formrepository->login(new Request(), "post", "/auth/login");
        $this->accessToken=$response['accessToken'];
        $this->refreshToken=$response['refreshToken'];
    }

    public function refreshUserToken()
    {
        $response = $this->formrepository->refreshToken(new Request(), "post", "/auth/refresh", $this->refreshToken);
        $this->accessToken=$response['accessToken'];
        $this->refreshToken=$response['refreshToken'];
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
        $forms=[];
        $this->login();
        $formsList = $this->getFormsList();
        if($formsList->ok()){
            $forms=$formsList->json();
        }elseif($formsList->unauthorized())
        {
            $this->refreshUserToken();
            $formsList = $this->getFormsList();
            if($formsList->ok()){
                $forms=$formsList->json();
            }
        }
        return view('endatix.index', compact('forms'));
    }

    public function show($id)
    {
        $url=env("ENDATIX_EMBEDED_URL")."/".$id;
        return view('endatix.show', compact('id', 'url'));
    }

    public function submission($id)
    {
        $this->login();
        $submissionList=$this->getSubmissionList($id);
        $submission=$submissionList->json();
        // return $submissionList;
        return view('endatix.submission', compact('submission'));
    }

    public function getSingleSubmission($form_id, $submission_id)
    {
        $this->login();
        $submission=$this->getSingleSubmissionApi($form_id, $submission_id);
        return $submission;
    }

    public function store(Request $request)
    {
        Log::info("Endatix Webhook Responses: ".json_encode($request->all()));
    }
}
