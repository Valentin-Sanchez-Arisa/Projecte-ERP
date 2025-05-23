<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('auth_token') || !$request->session()->has('user_id')) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            return redirect()->to('/login');
        }

        $userId = $request->session()->get('user_id');
        $user = DB::table('Users')->where('idUser', $userId)->first();

        if (!$user) {
            $request->session()->forget(['auth_token', 'user_id', 'user_name']);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No autenticado'], 401);
            }
            return redirect()->to('/login');
        }

        // Convertir el objeto a array antes de agregarlo al request
        $request->merge(['admin_user' => (array)$user]);
        
        // Obtener el nombre de la empresa
        $company = DB::table('UserAdministration')
            ->where('idEmpresa', $user->idEmpresa)
            ->first();
        
        $companyName = $company ? $company->Name : 'Empresa';
        
        // Hacer disponible el nombre del usuario y de la empresa para todas las vistas
        view()->share('userName', $user->Name);
        view()->share('companyName', $companyName);

        return $next($request);
    }
} 