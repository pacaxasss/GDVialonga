<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Contactos';
$pageDescription = 'Fale connosco. Morada do Complexo Desportivo de Vialonga, email e formulario de contacto.';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Contactos</span>
        </nav>
        <h1>Fale connosco</h1>
        <p>Pode aparecer pessoalmente, enviar email ou usar o formulário. Respondemos sempre.</p>
    </div>
</section>

<section class="section section-gray">
    <div class="container">
        <div class="contact-grid">

            <!-- Info -->
            <div>
                <h2 style="font-family:var(--font-display);font-size:1.25rem;font-weight:800;color:var(--gray-900);margin-bottom:1.5rem">Como nos encontrar</h2>
                <div class="contact-info-list">
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <div class="contact-info-label">Morada</div>
                            <div class="contact-info-value">Complexo Desportivo de Vialonga<br>Rua do Desportivo<br>2625-400 Vialonga, Vila Franca de Xira</div>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <div>
                            <div class="contact-info-label">Secretaria aberta</div>
                            <div class="contact-info-value">3.ª a 6.ª feira: 18h00 – 20h00<br>Sábados (jogos): a partir das 13h</div>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <div class="contact-info-label">Email Geral</div>
                            <div class="contact-info-value"><a href="mailto:geral@gdvialonga.pt" style="color:var(--gdv-blue)">geral@gdvialonga.pt</a></div>
                        </div>
                    </div>
                    <div class="contact-info-item">
                        <div class="contact-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <div>
                            <div class="contact-info-label">Formação (Sra. Rita)</div>
                            <div class="contact-info-value"><a href="mailto:formacao@gdvialonga.pt" style="color:var(--gdv-blue)">formacao@gdvialonga.pt</a></div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:2rem">
                    <h3 style="font-family:var(--font-display);font-size:.72rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.85rem">Redes Sociais</h3>
                    <div style="display:flex;gap:.6rem;flex-wrap:wrap">
                        <a href="https://facebook.com/gdvialonga" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:.45rem;padding:.55rem .95rem;background:#1877f2;color:#fff;font-family:var(--font-display);font-size:.78rem;font-weight:700">
                            Facebook
                        </a>
                        <a href="https://instagram.com/gdvialonga" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:.45rem;padding:.55rem .95rem;background:#e1306c;color:#fff;font-family:var(--font-display);font-size:.78rem;font-weight:700">
                            Instagram
                        </a>
                        <a href="https://youtube.com/@gdvialonga" target="_blank" rel="noopener noreferrer"
                           style="display:inline-flex;align-items:center;gap:.45rem;padding:.55rem .95rem;background:#ff0000;color:#fff;font-family:var(--font-display);font-size:.78rem;font-weight:700">
                            YouTube
                        </a>
                    </div>
                </div>

                <div style="margin-top:2rem">
                    <h3 style="font-family:var(--font-display);font-size:.72rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.75rem">No mapa</h3>
                    <iframe class="map-embed"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3104.0!2d-9.025!3d38.897!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzjCsDUzJzQ5LjIiTiA5wrAwMSczMC4wIlc!5e0!3m2!1spt!2spt!4v1"
                        allowfullscreen="" loading="lazy" title="Complexo Desportivo de Vialonga">
                    </iframe>
                </div>
            </div>

            <!-- Formulário -->
            <div class="form-card" style="position:sticky;top:120px">
                <h3 style="font-family:var(--font-display);font-weight:800;font-size:1.1rem;color:var(--gray-900);margin-bottom:.3rem">Envie-nos uma mensagem</h3>
                <p style="font-size:.8rem;color:var(--gray-400);margin-bottom:1.5rem">Respondemos habitualmente no próprio dia ou no dia seguinte.</p>
                <form id="contact-form" method="post" action="#" novalidate>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="c-nome">O seu nome *</label>
                            <input class="form-input" type="text" id="c-nome" name="nome" required placeholder="Nome">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="c-email">Email *</label>
                            <input class="form-input" type="email" id="c-email" name="email" required placeholder="email@exemplo.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="c-tel">Telemóvel</label>
                            <input class="form-input" type="tel" id="c-tel" name="telefone" placeholder="9XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="c-assunto">Assunto *</label>
                            <select class="form-select" id="c-assunto" name="assunto" required>
                                <option value="" disabled selected>Escolher...</option>
                                <option>Informações Gerais</option>
                                <option>Formação / Inscrições</option>
                                <option>Equipa Sénior</option>
                                <option>Parceria ou Patrocínio</option>
                                <option>Imprensa</option>
                                <option>Outro</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label" for="c-msg">Mensagem *</label>
                            <textarea class="form-textarea" id="c-msg" name="mensagem" required placeholder="Escreva aqui..." style="min-height:130px"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <button type="submit" class="btn btn-primary btn-lg btn-full">Enviar mensagem</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
