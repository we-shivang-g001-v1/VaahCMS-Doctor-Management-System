<?php

use VaahCms\Modules\Appointments\Http\Controllers\Backend\patientsController;

Route::group(
    [
        'prefix' => 'backend/appointments/patients',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [patientsController::class, 'getAssets'])
        ->name('vh.backend.appointments.patients.assets');
    /**
     * Get List
     */
    Route::get('/', [patientsController::class, 'getList'])
        ->name('vh.backend.appointments.patients.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [patientsController::class, 'updateList'])
        ->name('vh.backend.appointments.patients.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [patientsController::class, 'deleteList'])
        ->name('vh.backend.appointments.patients.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [patientsController::class, 'fillItem'])
        ->name('vh.backend.appointments.patients.fill');

    /**
     * Create Item
     */
    Route::post('/', [patientsController::class, 'createItem'])
        ->name('vh.backend.appointments.patients.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [patientsController::class, 'getItem'])
        ->name('vh.backend.appointments.patients.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [patientsController::class, 'updateItem'])
        ->name('vh.backend.appointments.patients.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [patientsController::class, 'deleteItem'])
        ->name('vh.backend.appointments.patients.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [patientsController::class, 'listAction'])
        ->name('vh.backend.appointments.patients.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [patientsController::class, 'itemAction'])
        ->name('vh.backend.appointments.patients.item.action');

    //---------------------------------------------------------

});
