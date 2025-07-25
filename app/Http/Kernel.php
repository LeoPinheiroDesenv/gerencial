<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'valid' => \App\Http\Middleware\Valid::class,
        'validNFCe' => \App\Http\Middleware\validNFCe::class,
        'control' => \App\Http\Middleware\Control::class,
        'csv' => \App\Http\Middleware\Csv::class,
        'pedidoAtivo' => \App\Http\Middleware\PedidoAtivo::class,
        'mesaAtiva' => \App\Http\Middleware\MesaAtiva::class,
        'pedidoEstaAtivo' => \App\Http\Middleware\PedidoEstaAtivo::class,
        'authApp' => \App\Http\Middleware\AuthApp::class,
        'verificaEmpresa' => \App\Http\Middleware\VerificaEmpresa::class,
        'validaEmpresa' => \App\Http\Middleware\ValidaEmpresa::class,
        'validaAcesso' => \App\Http\Middleware\ValidaAcesso::class,

        'limiteProdutos' => \App\Http\Middleware\LimiteProdutos::class,
        'limiteClientes' => \App\Http\Middleware\LimiteClientes::class,
        'limiteFornecedor' => \App\Http\Middleware\LimiteFornecedor::class,

        'limiteNFe' => \App\Http\Middleware\LimiteNFe::class,
        'limiteNFCe' => \App\Http\Middleware\LimiteNFCe::class,
        'limiteCTe' => \App\Http\Middleware\LimiteCTe::class,
        'limiteMDFe' => \App\Http\Middleware\LimiteMDFe::class,
        'limiteNFSe' => \App\Http\Middleware\LimiteNFSe::class,
        'validaEvento' => \App\Http\Middleware\ValidaEvento::class,
        'limiteEvento' => \App\Http\Middleware\LimiteEvento::class,
        'limiteUsuarios' => \App\Http\Middleware\LimiteUsuarios::class,
        'verificaContratoAssinado' => \App\Http\Middleware\VerificaContratoAssinado::class,
        'validaEcommerce' => \App\Http\Middleware\ValidaEcommerce::class,
        'acessoUsuario' => \App\Http\Middleware\AcessoUsuario::class,
        'usuariosLogado' => \App\Http\Middleware\UsuariosLogado::class,
        'limiteArmazenamento' => \App\Http\Middleware\LimiteArmazenamento::class,
        'validaRepresentante' => \App\Http\Middleware\ValidaRepresentante::class,
        'authEcommerce' => \App\Http\Middleware\AuthEcommerce::class,
        'authPdv' => \App\Http\Middleware\AuthPdv::class,
        'verificaPesquisa' => \App\Http\Middleware\VerificaPesquisa::class,
        'authAppComanda' => \App\Http\Middleware\AuthAppComanda::class,
        'authDelivery' => \App\Http\Middleware\AuthDelivery::class,
    ];
    
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'valid' => \App\Http\Middleware\Valid::class,
        'validNfce' => \App\Http\Middleware\ValidNfce::class,
        'authh' => \App\Http\Middleware\Authh::class,
        'validaEmpresa' => \App\Http\Middleware\ValidaEmpresa::class,
        'verificaEmpresa' => \App\Http\Middleware\VerificaEmpresa::class,
        'validaPlano' => \App\Http\Middleware\ValidaPlano::class,
        'validaNFe' => \App\Http\Middleware\ValidaNFe::class,
        'validaNFCe' => \App\Http\Middleware\ValidaNFCe::class,
        'verificaMaster' => \App\Http\Middleware\VerificaMaster::class,
    ];
}
