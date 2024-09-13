<?php

use VaahCms\Modules\Appointments\Http\Controllers\Backend\doctorsController;

Route::group(
    [
        'prefix' => 'backend/appointments/doctors',
        
        'middleware' => ['web', 'has.backend.access'],
        
],
function () {
    /**
     * Get Assets
     */
    Route::get('/assets', [doctorsController::class, 'getAssets'])
        ->name('vh.backend.appointments.doctors.assets');
    /**
     * Get List
     */
    Route::get('/', [doctorsController::class, 'getList'])
        ->name('vh.backend.appointments.doctors.list');
    /**
     * Update List
     */
    Route::match(['put', 'patch'], '/', [doctorsController::class, 'updateList'])
        ->name('vh.backend.appointments.doctors.list.update');
    /**
     * Delete List
     */
    Route::delete('/', [doctorsController::class, 'deleteList'])
        ->name('vh.backend.appointments.doctors.list.delete');


    /**
     * Fill Form Inputs
     */
    Route::any('/fill', [doctorsController::class, 'fillItem'])
        ->name('vh.backend.appointments.doctors.fill');

    /**
     * Create Item
     */
    Route::post('/', [doctorsController::class, 'createItem'])
        ->name('vh.backend.appointments.doctors.create');
    /**
     * Get Item
     */
    Route::get('/{id}', [doctorsController::class, 'getItem'])
        ->name('vh.backend.appointments.doctors.read');
    /**
     * Update Item
     */
    Route::match(['put', 'patch'], '/{id}', [doctorsController::class, 'updateItem'])
        ->name('vh.backend.appointments.doctors.update');
    /**
     * Delete Item
     */
    Route::delete('/{id}', [doctorsController::class, 'deleteItem'])
        ->name('vh.backend.appointments.doctors.delete');

    /**
     * List Actions
     */
    Route::any('/action/{action}', [doctorsController::class, 'listAction'])
        ->name('vh.backend.appointments.doctors.list.actions');

    /**
     * Item actions
     */
    Route::any('/{id}/action/{action}', [doctorsController::class, 'itemAction'])
        ->name('vh.backend.appointments.doctors.item.action');

    //---------------------------------------------------------

});
