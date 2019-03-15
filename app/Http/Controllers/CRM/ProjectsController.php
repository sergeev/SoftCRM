<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectsStoreRequest;
use App\Services\ProjectsService;
use App\Services\SystemLogService;
use App\Traits\Language;
use View;
use Illuminate\Http\Request;
Use Illuminate\Support\Facades\Redirect;
use Config;

class ProjectsController extends Controller
{
    use Language;

    private $systemLogs;
    private $language;
    private $projectsModel;
    private $projectsService;

    public function __construct()
    {
        $this->systemLogs = new SystemLogService();
        $this->projectsService = new ProjectsService();
    }

    private function getDataAndPagination()
    {
        $dataWithProjects = [
            'projects' => $this->projectsService->getProjects(),
            'projectsPaginate' => $this->projectsService->getPagination()
        ];

        return $dataWithProjects;
    }

    public function index()
    {
        return View::make('crm.projects.index')->with(
            [
                'projects' => $this->getDataAndPagination(),
                'inputText' => $this->getMessage('messages.InputText')
            ]);
    }

    public function create()
    {
        $dataForView = $this->projectsService->collectDataForView();

        return View::make('crm.projects.create')->with(
            [
                'dataOfClients' => $dataForView->clients,
                'dataOfCompanies' => $dataForView->companies,
                'dataOfDeals' => $dataForView->deals,
                'inputText' => $this->getMessage('messages.InputText')
            ]);
    }

    public function show($projectId)
    {
        return View::make('crm.projects.show')
            ->with([
                'projects' => $this->projectsService->getProject($projectId),
                'inputText' => $this->getMessage('messages.InputText')
            ]);
    }

    public function edit($projectId)
    {
        $dataForView = $this->projectsService->collectDataForView();

        return View::make('crm.projects.edit')
            ->with([
                'projects' => $this->projectsService->getProject($projectId),
                'clients' => $dataForView->clients,
                'deals' => $dataForView->deals,
                'companies' => $dataForView->companies
            ]);
    }

    public function store(ProjectsStoreRequest $request)
    {
        if ($project = $this->projectsService->execute($request->validated())) {
            $this->systemLogs->insertSystemLogs('Project has been add with id: '. $project, 200);
            return Redirect::to('projects')->with('message_success', $this->getMessage('messages.SuccessProjectsStore'));
        } else {
            return Redirect::back()->with('message_success', $this->getMessage('messages.ErrorProjectsStore'));
        }
    }

    public function update(Request $request, int $projectId)
    {
        if ($this->projectsService->update($projectId, $request->all())) {
            return Redirect::to('projects')->with('message_success', $this->getMessage('messages.SuccessProjectsStore'));
        } else {
            return Redirect::back()->with('message_danger', $this->getMessage('messages.ErrorProjectsStore'));
        }
    }

    public function destroy($projectId)
    {
        $projectsDetails = $this->projectsService->getProject($projectId);
        $projectsDetails->delete();

        $this->systemLogs->insertSystemLogs('ProjectsModel has been deleted with id: ' . $projectsDetails->id, 200);

        return Redirect::to('projects')->with('message_success', $this->getMessage('messages.SuccessProjectsDelete'));
    }

    public function processSetIsActive($projectId, $value)
    {
        if ($this->projectsService->loadIsActiveFunction($projectId, $value)) {
            $this->systemLogs->insertSystemLogs('ProjectsModel has been enabled with id: ' . $projectId, 200);
            return Redirect::back()->with('message_success', $this->getMessage('messages.SuccessProjectsActive'));
        } else {
            return Redirect::back()->with('message_danger', $this->getMessage('messages.ProjectsIsActived'));
        }
    }

    public function search()
    {
        return true; // TODO
    }
}
