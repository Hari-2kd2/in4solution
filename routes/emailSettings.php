<?php
Route::group(['middleware' => ['preventbackbutton','auth']], function(){

    Route::group(['prefix' => 'emailSettings'], function () {
        Route::get('/',['as' => 'emailSettings.index', 'uses'=>'emailSettings\EmailSettingsController@index']);
        Route::post('/store',['as' => 'emailSettings.store', 'uses'=>'emailSettings\EmailSettingsController@store']);
        Route::post('/update/{id}',['as' => 'emailSettings.update', 'uses'=>'emailSettings\EmailSettingsController@update']);
        Route::get('/delete/{id}', ['as' => 'emailSettings.delete', 'uses' => 'emailSettings\EmailSettingsController@destroy']);
        Route::get('/keypeople-search',['as' => 'emailSettings.keypeopleSearch', 'uses'=>'emailSettings\EmailSettingsController@keypeopleSearch']);

        Route::post('/hrStore',['as' => 'emailSettings.hrStore', 'uses'=>'emailSettings\EmailSettingsController@hrStore']);
        Route::post('/hrUpdate/{id}',['as' => 'emailSettings.hrUpdate', 'uses'=>'emailSettings\EmailSettingsController@hrUpdate']);
        Route::get('/hrDelete/{id}', ['as' => 'emailSettings.hrDelete', 'uses' => 'emailSettings\EmailSettingsController@hrDestroy']);
    });

});

