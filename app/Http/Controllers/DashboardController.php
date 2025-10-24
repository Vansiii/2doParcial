<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $usuario = auth()->user();
        $roles = $usuario->roles;
        
        return view('dashboard', compact('usuario', 'roles'));
    }
}
