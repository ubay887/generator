<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreGeneratorRequest;
use Illuminate\Support\Facades\Artisan;
use App\Generators\{
    ControllerGenerator,
    MenuGenerator,
    ModelGenerator,
    MigrationGenerator,
    PermissionGenerator,
    RequestGenerator,
    RouteGenerator,
    ViewComposerGenerator
};
use App\Generators\Views\{
    ActionViewGenerator,
    CreateViewGenerator,
    EditViewGenerator,
    FormViewGenerator,
    IndexViewGenerator,
    ShowViewGenerator,
};


class GeneratorController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('generators.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreGeneratorRequest $request)
    {
        (new MenuGenerator)->generate($request->validated());

        return ['success'];die;

        if ($request->generate_type == 'all') {
            $this->generateAll($request->validated());
        } else {
            (new ModelGenerator)->generate($request->validated());

            (new MigrationGenerator)->generate($request->validated());
        }

        return response()->json(['success'], Response::HTTP_OK);
    }

    /**
     * Generate all CRUD modules.
     *
     * @param array $request
     * @return void
     */
    protected function generateAll(array $request)
    {
        (new ModelGenerator)->generate($request);
        (new MigrationGenerator)->generate($request);
        (new ControllerGenerator)->generate($request);
        (new RequestGenerator)->generate($request);

        (new IndexViewGenerator)->generate($request);
        (new CreateViewGenerator)->generate($request);
        (new ShowViewGenerator)->generate($request);
        (new EditViewGenerator)->generate($request);
        (new ActionViewGenerator)->generate($request);
        (new FormViewGenerator)->generate($request);

        (new MenuGenerator)->generate($request);
        (new RouteGenerator)->generate($request);
        (new PermissionGenerator)->generate($request);

        if (in_array('foreignId', $request['data_types'])) {
            (new ViewComposerGenerator)->generate($request);
        }

        Artisan::call('optimize:clear');
        Artisan::call('migrate');
    }

    /**
     * Get all sidebar menus on config by index.
     *
     * @param int $index
     * @return \Illuminate\Http\Response
     */
    public function getSidebarMenus(int $index)
    {
        abort_if(!request()->ajax(), 403);

        $sidebar = config('generator.sidebars')[$index];

        return response()->json($sidebar['menus'], Response::HTTP_OK);
    }
}
