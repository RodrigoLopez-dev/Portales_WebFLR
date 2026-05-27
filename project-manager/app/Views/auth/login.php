<style>
    :root {
        --flr-burdeo: #AB0A3D;
        --flr-magenta: #D70073;
        --flr-blanco: #FFFFFF;
        --flr-negro: #000000;
        --flr-amarillo: #FFC72C;
        --flr-verde: #2DB034;
        --flr-celeste: #009FDF;
        --flr-morado: #512598;
        --flr-bg: #FFF5FA;
        --flr-borde: #ead3dc;
        --flr-texto: #1f2937;
    }

    .login-page {
        min-height: calc(100vh - 120px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 12px;
        background: linear-gradient(135deg, #ffffff 0%, var(--flr-bg) 100%);
    }

    .login-shell {
        width: 100%;
        max-width: 1050px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: #fff;
        border-radius: 22px;
        overflow: hidden;
        box-shadow: 0 18px 45px rgba(171, 10, 61, .16);
        border: 1px solid rgba(171, 10, 61, .12);
    }

    .login-brand {
        position: relative;
        min-height: 620px;
        padding: 56px 48px;
        color: #fff;
        background:
            linear-gradient(135deg, rgba(171, 10, 61, .98), rgba(215, 0, 115, .88)),
            radial-gradient(circle at top right, rgba(255,199,44,.28), transparent 32%);
    }

    .login-brand::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.06) 1px, transparent 1px);
        background-size: 34px 34px;
        opacity: .25;
        pointer-events: none;
    }

    .login-brand-content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .login-logo {
        width: 98px;
        height: 98px;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.22);
        margin-bottom: 22px;
        
    }


    .login-logo img {
        width: 88px;
        height: auto;
        display: block;
    }

    .login-brand h2 {
        font-size: 2rem;
        line-height: 1.18;
        font-weight: 800;
        margin: 0 0 18px;
        color: #fff;
    }

    .login-brand p {
        color: rgba(255,255,255,.86);
        font-size: 1.02rem;
        line-height: 1.65;
        max-width: 420px;
        margin-bottom: 34px;
    }

    .login-feature {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        margin-bottom: 22px;
    }

    .login-feature-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.16);
        color: #fff;
        flex: 0 0 auto;
    }

    .login-feature:nth-of-type(1) .login-feature-icon {
        box-shadow: inset 0 0 0 2px rgba(255,199,44,.55);
    }

    .login-feature:nth-of-type(2) .login-feature-icon {
        box-shadow: inset 0 0 0 2px rgba(0,159,223,.55);
    }

    .login-feature:nth-of-type(3) .login-feature-icon {
        box-shadow: inset 0 0 0 2px rgba(45,176,52,.55);
    }

    .login-feature strong {
        display: block;
        margin-bottom: 3px;
        color: #fff;
    }

    .login-feature span {
        color: rgba(255,255,255,.78);
        font-size: .92rem;
    }

    .login-security {
        margin-top: auto;
        display: flex;
        gap: 10px;
        align-items: center;
        color: rgba(255,255,255,.82);
        font-size: .92rem;
    }

    .login-form-panel {
        padding: 58px 54px;
        display: flex;
        align-items: center;
    }

    .login-form-inner {
        width: 100%;
        max-width: 460px;
        margin: 0 auto;
    }

    .login-eyebrow {
        color: var(--flr-magenta);
        font-weight: 800;
        text-align: center;
        margin-bottom: 8px;
    }

    .login-title {
        text-align: center;
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--flr-negro);
        margin-bottom: 32px;
    }

    .login-form-inner .form-label {
        font-weight: 700;
        color: var(--flr-texto);
        margin-bottom: 8px;
    }

    .login-input-wrap {
        position: relative;
    }

    .login-input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--flr-burdeo);
        z-index: 2;
    }

    .login-form-inner .form-control {
        min-height: 52px;
        border-radius: 12px;
        padding-left: 48px;
        border-color: var(--flr-borde);
        box-shadow: 0 4px 12px rgba(171, 10, 61, .05);
    }

    .login-form-inner .form-control:focus {
        border-color: var(--flr-magenta);
        box-shadow: 0 0 0 .22rem rgba(215, 0, 115, .16);
    }

    .login-submit {
        min-height: 52px;
        border-radius: 12px;
        font-weight: 800;
        border: none;
        background: linear-gradient(135deg, var(--flr-burdeo), var(--flr-magenta));
        box-shadow: 0 12px 22px rgba(171, 10, 61, .28);
    }

    .login-submit:hover {
        background: linear-gradient(135deg, var(--flr-magenta), var(--flr-burdeo));
    }

    .login-divider {
        display: flex;
        align-items: center;
        gap: 14px;
        color: #9b6b7d;
        margin: 26px 0;
        font-size: .9rem;
    }

    .login-divider::before,
    .login-divider::after {
        content: "";
        height: 1px;
        background: var(--flr-borde);
        flex: 1;
    }

    .login-google {
        min-height: 50px;
        border-radius: 12px;
        font-weight: 700;
        color: var(--flr-texto);
        border-color: var(--flr-borde);
        background: #fff;
    }

    .login-google:hover {
        border-color: var(--flr-magenta);
        background: var(--flr-bg);
    }

    .login-note {
        color: #6b7280;
        font-size: .86rem;
        text-align: center;
        margin-top: 16px;
        line-height: 1.5;
    }

    @media (max-width: 991.98px) {
        .login-shell {
            grid-template-columns: 1fr;
            max-width: 560px;
        }

        .login-brand {
            display: none;
        }

        .login-form-panel {
            padding: 40px 28px;
        }
    }


</style>

<div class="login-page">
    <div class="login-shell">
        <aside class="login-brand">
            <div class="login-brand-content">
                <!-- <div class="login-logo" aria-hidden="true">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6.5L12 2L20 6.5L12 11L4 6.5Z" stroke="white" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M4 12L12 16.5L20 12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 17.5L12 22L20 17.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div> -->

                <div class="login-logo">
                    <img src="<?= base_url('logo/logo_flr.png') ?>" alt="Fundación Las Rosas">
                </div>

                <h2>Plataforma de Gestión de Proyectos</h2>
                <p>Administra, controla y da seguimiento a solicitudes, documentos, planificación y evidencias en un solo lugar.</p>

                <div class="login-feature">
                    <div class="login-feature-icon" aria-hidden="true">✓</div>
                    <div>
                        <strong>Seguimiento en tiempo real</strong>
                        <span>Visualiza el avance y estado de cada proyecto.</span>
                    </div>
                </div>

                <div class="login-feature">
                    <div class="login-feature-icon" aria-hidden="true">▣</div>
                    <div>
                        <strong>Documentación centralizada</strong>
                        <span>Organiza documentos, anexos y antecedentes.</span>
                    </div>
                </div>

                <div class="login-feature">
                    <div class="login-feature-icon" aria-hidden="true">!</div>
                    <div>
                        <strong>Notificaciones inteligentes</strong>
                        <span>Recibe alertas importantes de los procesos clave.</span>
                    </div>
                </div>

                <div class="login-security">
                    <span aria-hidden="true">🔒</span>
                    <span>Sistema seguro · Acceso para usuarios registrados</span>
                </div>
            </div>
        </aside>

        <section class="login-form-panel">
            <div class="login-form-inner">
                <div class="login-eyebrow">Bienvenido nuevamente</div>
                <h1 class="login-title">Iniciar sesión</h1>

                <form method="POST" action="<?= e(base_url('/login')) ?>">
                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <div class="login-input-wrap">
                            <span class="login-input-icon" aria-hidden="true">✉</span>
                            <input type="email" name="email" class="form-control" placeholder="usuario@empresa.com" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <div class="login-input-wrap">
                            <span class="login-input-icon" aria-hidden="true">🔐</span>
                            <input type="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary login-submit">Ingresar</button>
                    </div>
                </form>

                <div class="login-divider">o</div>

                <div class="d-grid">
                    <a href="<?= e(base_url('/auth/google')) ?>" class="btn login-google">
                        <span style="color:#4285F4;font-weight:800;margin-right:8px;">G</span>
                        Ingresar con Google
                    </a>
                </div>

                <div class="login-note">
                    Solo podrán ingresar con Google los usuarios ya registrados en el sistema.
                </div>
            </div>
        </section>
    </div>
</div>
