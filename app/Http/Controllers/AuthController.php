<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function proses( Request $request ) {

    	$request->validate([
    		'email'	=> 'required',
    		'password'	=> 'required',
    	]);

    	$credential = request(['email', 'password']);
    	if ( Auth::attempt( $credential ) ) {

    		if ( Auth::user()->role == "admin" ) {
    			return redirect('welcome');

    		} else if ( Auth::user()->role == "teacher" ) {
                return redirect('dashboard_teacher');

    		} else {
                // student
                return redirect('dashboard-student');
    		}

    	} else {

    		echo "okee err";
    	}
    }
    public function signup( Request $request ) {
		$data = $request->validate([
			'name'	=> 'required',
    		'email'	=> 'required',
			'password' => 'required|confirmed',
			'teacher' => 'required'
			
    	]);
		$data['password'] = bcrypt($data['password']);
		User::create($data);
		return redirect('/');
    }
	public function logoutt(Request $request): RedirectResponse
	{
		Auth::logout();
		$request->session()->invalidate();
 
		$request->session()->regenerateToken();
		return redirect('/');
	}
	Public function redirect() {
        return Socialite::driver(driver:'google')->redirect();
    }
    Public function googleCallback ()
    {
        $user = Socialite::driver('google')->user();
		$userDatabase = User::where('google_id', $user->getId())->first();
		$token = $user->token;
		session(['google_token' => $token]);
		if(!$userDatabase){
			$data= [
				'google_id' =>$user->getId(),
				'name' => $user->getName(),
				'email' =>$user->getEmail(),
				'role' =>'Student',
			];
		$newUser = User::firstOrCreate(['email' => $data['email']],$data);
		$newUser = User::firstOrCreate(['role' => $data['role']],$data);

		auth('web')->login($newUser);
		session()->regenerate();
		return redirect()->route('dashboard-student');
		}
		auth('web')->login($userDatabase);
		session()->regenerate();
		return redirect()->route('dashboard-student');
    }
}
