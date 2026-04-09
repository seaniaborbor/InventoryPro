<?php

namespace App\Controllers;

class Guide extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login');
        }

        $data = [
            'title' => 'User Guide',
            'activePage' => 'guide',
        ];

        return view('guide/index', $data);
    }
}
