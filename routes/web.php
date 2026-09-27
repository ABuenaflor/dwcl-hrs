<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Applicant;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareersController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Faculty;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use Illuminate\Support\Facades\Route;

/*
| Public: landing page and job vacancies
*/
Route::get('/', [CareersController::class, 'home'])->name('home');
Route::get('/careers', [CareersController::class, 'index'])->name('careers.index');
Route::get('/careers/{posting}', [CareersController::class, 'show'])->name('careers.show');

/*
| Authentication — one sign-in for every role
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/register/employee', [AuthController::class, 'showEmployeeRequest'])->name('register.employee');
    Route::post('/register/employee', [AuthController::class, 'requestEmployeeAccount']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/account/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    // Private files — authorization happens in the controller.
    Route::get('/documents/{document}', [DocumentController::class, 'application'])->name('documents.show');
    Route::get('/evidence/{score}', [DocumentController::class, 'evidence'])->name('evidence.show');
});

/*
| Applicants
*/
Route::middleware(['auth', 'role:applicant'])->prefix('applicant')->name('applicant.')->group(function () {
    Route::get('/', [Applicant\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Applicant\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Applicant\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/apply/{posting}', [Applicant\ApplicationController::class, 'create'])->name('apply');
    Route::post('/apply/{posting}', [Applicant\ApplicationController::class, 'store'])->name('apply.store');
    Route::get('/applications/{application}', [Applicant\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/withdraw', [Applicant\ApplicationController::class, 'withdraw'])->name('applications.withdraw');
    Route::post('/applications/{application}/respond', [Applicant\ApplicationController::class, 'respond'])->name('applications.respond');
});

/*
| Faculty self-rating
*/
Route::middleware(['auth', 'role:faculty'])->prefix('faculty')->name('faculty.')->group(function () {
    Route::get('/', [Faculty\RankingController::class, 'index'])->name('dashboard');
    Route::post('/rankings', [Faculty\RankingController::class, 'store'])->name('rankings.store');
    Route::get('/rankings/{ranking}', [Faculty\RankingController::class, 'show'])->name('rankings.show');
    Route::put('/rankings/{ranking}', [Faculty\RankingController::class, 'update'])->name('rankings.update');
    Route::post('/rankings/{ranking}/submit', [Faculty\RankingController::class, 'submit'])->name('rankings.submit');
    Route::get('/rankings/{ranking}/certificate', [Faculty\RankingController::class, 'certificate'])->name('rankings.certificate');
});

/*
| HRDO and ranking committees
*/
Route::middleware(['auth', 'role:admin,drc,crtc,vp'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/rankings', [Admin\RankingController::class, 'index'])->name('rankings.index');
    Route::get('/rankings/{ranking}', [Admin\RankingController::class, 'show'])->name('rankings.show');
    Route::put('/rankings/{ranking}', [Admin\RankingController::class, 'update'])->name('rankings.update');
    Route::post('/rankings/{ranking}/return', [Admin\RankingController::class, 'return'])->name('rankings.return');
    Route::get('/rankings/{ranking}/certificate', [Admin\RankingController::class, 'certificate'])->name('rankings.certificate');

    Route::middleware('role:admin')->group(function () {
        Route::resource('postings', Admin\JobPostingController::class)->except('show');
        Route::get('/postings/{posting}/ranking', [Admin\JobPostingController::class, 'ranking'])->name('postings.ranking');

        Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
        Route::put('/applications/{application}/scores', [Admin\ApplicationController::class, 'scores'])->name('applications.scores');
        Route::patch('/applications/{application}/status', [Admin\ApplicationController::class, 'status'])->name('applications.status');

        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [Admin\UserController::class, 'status'])->name('users.status');

        Route::get('/rubrics', [Admin\RubricController::class, 'index'])->name('rubrics.index');
        Route::get('/rubrics/{rubric}', [Admin\RubricController::class, 'show'])->name('rubrics.show');
        Route::post('/rubrics/{rubric}/items', [Admin\RubricController::class, 'storeItem'])->name('rubrics.items.store');
        Route::put('/rubric-items/{item}', [Admin\RubricController::class, 'updateItem'])->name('rubrics.items.update');
        Route::delete('/rubric-items/{item}', [Admin\RubricController::class, 'destroyItem'])->name('rubrics.items.destroy');
        Route::put('/academic-ranks', [Admin\RubricController::class, 'updateRanks'])->name('ranks.update');

        Route::get('/setup', [Admin\SetupController::class, 'index'])->name('setup.index');
        Route::post('/setup/campuses', [Admin\SetupController::class, 'storeCampus'])->name('setup.campuses.store');
        Route::put('/setup/campuses/{campus}', [Admin\SetupController::class, 'updateCampus'])->name('setup.campuses.update');
        Route::post('/setup/departments', [Admin\SetupController::class, 'storeDepartment'])->name('setup.departments.store');
        Route::put('/setup/departments/{department}', [Admin\SetupController::class, 'updateDepartment'])->name('setup.departments.update');
        Route::delete('/setup/departments/{department}', [Admin\SetupController::class, 'destroyDepartment'])->name('setup.departments.destroy');

        Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/applications.csv', [Admin\ReportController::class, 'applicationsCsv'])->name('reports.applications.csv');
        Route::get('/reports/rankings.csv', [Admin\ReportController::class, 'rankingsCsv'])->name('reports.rankings.csv');
    });
});
