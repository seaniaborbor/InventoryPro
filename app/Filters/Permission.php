<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Permission implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('permission');

        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/auth/login')->with('error', 'Please login to access this page');
        }

        $requiredPermissions = array_values(array_filter((array) $arguments));

        if (empty($requiredPermissions)) {
            return null;
        }

        $requireAll = false;
        if (str_starts_with($requiredPermissions[0], 'all:')) {
            $requireAll = true;
            $requiredPermissions[0] = substr($requiredPermissions[0], 4);
        }

        if ($requireAll) {
            foreach ($requiredPermissions as $permissionName) {
                if (!has_permission($permissionName)) {
                    return $this->deny($request);
                }
            }

            return null;
        }

        foreach ($requiredPermissions as $permissionName) {
            if (has_permission($permissionName)) {
                return null;
            }
        }

        return $this->deny($request);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }

    private function deny(RequestInterface $request)
    {
        if ($request->isAJAX()) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'You do not have permission to perform this action.',
                ]);
        }

        return redirect()->to('/dashboard')->with('error', 'Access denied. Your role does not have permission for this action.');
    }
}
