<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;

class ConfirmPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Confirm Password Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password confirmations and
    | uses a simple trait to include the behavior. You're free to explore
    | this trait and override any functions that require customization.
    |
    */

    use ConfirmsPasswords;

    /**
     * Where to redirect users when the intended url fails.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Reglas de validación de la contraseña.
     *
     * Sobreescribe las del trait (`required|current_password:web`, sin
     * `string`): si `password` llega como array (petición manipulada), la
     * regla `current_password` llama a `Hash::check()` → `password_verify()`
     * con un array y PHP lanza un `TypeError` (500) en vez de un error de
     * validación. `bail` es imprescindible: sin él, Laravel ejecuta todas
     * las reglas del campo aunque `string` ya haya fallado, y `current_password`
     * se dispara igual; con `bail`, se detiene en la primera regla que falla.
     *
     * @return array<string, string>
     */
    protected function rules()
    {
        return ['password' => 'bail|required|string|current_password:web'];
    }
}
