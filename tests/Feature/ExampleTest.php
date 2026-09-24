<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz redirige a /home, y un invitado acaba en el login.
     */
    public function test_la_raiz_redirige_a_home(): void
    {
        $this->get('/')->assertRedirect('/home');
    }

    public function test_home_exige_sesion(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_la_pantalla_de_login_carga(): void
    {
        $this->get('/login')->assertOk();
    }
}
