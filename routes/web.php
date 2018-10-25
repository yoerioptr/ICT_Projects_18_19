<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/* Show users per trip as an organizer */
Route::get('user/{sUserName}/trip/travellers', 'UserDataController@showUsersAsMentor');
Route::post('user/{sUserName}/trip/travellers', 'UserDataController@showUsersAsMentor');
//Route::get('user/trip/travellers', 'UserDataController@showUsersAsMentor');
//Route::post('user/trip/travellers', 'UserDataController@showUsersAsMentor');

route::get('user/Form/form', 'RegisterController@form');
route::post('user/Form/form', 'RegisterController@formPost');

//Get active trip to link to organizers
route::get('admin/linkorganisator/', 'ActiveTripOrganizerController@showForm');

route::get('admin/user/default', 'AdminUserController@createForm');
route::post('admin/user/default', 'AdminUserController@createUser');

Route::get('admin/info', 'AdminInfoController@getInfo');
Route::post('admin/info', 'AdminInfoController@updateInfo');

Route::get('/info','AdminInfoController@showInfo')->name('info');

Route::get('userinfo/{sUserName}', 'UserDataController@showUserData');

Route::get('/', function () {
    return redirect('info');
});
