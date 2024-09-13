<?php
use VaahCms\Modules\Appointments\Http\Controllers\Backend\patientsController;
/*
 * API url will be: <base-url>/public/api/appointments/patients
 */
Route::group(
    [
        'prefix' => 'appointments/patients',
        'namespace' => 'Backend',
    ],
function () {

    /**
     * Get Assets
     */
    Route::get('/assets', [patientsController::class, 'getAssets'])
        ->name('vh.backend.appointments.api.patients.assets');
    /**
     * Get List
     */
    Route::get('/', [patientsController::class, 'getList'])
        ->name('vh.backend.appointments.api.patients.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [patientsController::class, 'updateList'])
        ->name('vh.backend.appointments.api.patients.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [patientsController::class, 'deleteList'])
        ->name('vh.backend.appointments.api.patients.list.delete');


    /**
     * Create Item
     */
    Route::post('/', [patientsController::class, 'createItem'])
        ->name('vh.backend.appointments.api.patients.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [patientsController::class, 'getItem'])
        ->name('vh.backend.appointments.api.patients.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [patientsController::class, 'updateItem'])
        ->name('vh.backend.appointments.api.patients.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [patientsController::class, 'deleteItem'])
        ->name('vh.backend.appointments.api.patients.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [patientsController::class, 'listAction'])
        ->name('vh.backend.appointments.api.patients.list.action');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [patientsController::class, 'itemAction'])
        ->name('vh.backend.appointments.api.patients.item.action');



});
