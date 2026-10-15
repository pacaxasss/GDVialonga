<footer class="site-footer">
    <div class="footer-top">
        <div class="container footer-grid">
            <div>
                <div class="footer-logo">
                    <img src="<?= url('assets/images/logo.png') ?>" alt="GD Vialonga" class="footer-logo-img" data-hide-on-error>
                    <div>
                        <strong class="footer-club-name">GD Vialonga</strong>
                        <small class="footer-club-sub">Grupo Desportivo de Vialonga</small>
                    </div>
                </div>
                <p class="footer-desc">Fundado em 1961, construído com trabalho, e vivido com orgulho. O GDV é Vialonga &mdash; sempre foi.</p>
                <div class="footer-social">
                    <a href="https://facebook.com/gdvialonga" target="_blank" rel="noopener noreferrer" class="footer-social-link">Facebook</a>
                    <a href="https://instagram.com/gdvialonga" target="_blank" rel="noopener noreferrer" class="footer-social-link">Instagram</a>
                    <a href="https://youtube.com/@gdvialonga" target="_blank" rel="noopener noreferrer" class="footer-social-link">YouTube</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Páginas</h4>
                <ul class="footer-links">
                    <li><a href="<?= url() ?>">Início</a></li>
                    <li><a href="<?= url('o-clube') ?>">O Clube</a></li>
                    <li><a href="<?= url('noticias') ?>">Notícias</a></li>
                    <li><a href="<?= url('futebol') ?>">Futebol</a></li>
                    <li><a href="<?= url('plantel') ?>">Plantéis</a></li>
                    <li><a href="<?= url('formacao') ?>">Formação</a></li>
                    <li><a href="<?= url('loja') ?>">Loja Oficial</a></li>
                    <li><a href="<?= url('patrocinadores') ?>">Patrocinadores</a></li>
                    <li><a href="<?= url('contactos') ?>">Contactos</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">Escalões</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('plantel') ?>">Seniores</a></li>
                    <li><a href="<?= url('plantel') ?>">Juniores (Sub-19)</a></li>
                    <li><a href="<?= url('formacao') ?>">Juvenis (Sub-17)</a></li>
                    <li><a href="<?= url('formacao') ?>">Iniciados (Sub-15)</a></li>
                    <li><a href="<?= url('formacao') ?>">Infantis (Sub-13)</a></li>
                    <li><a href="<?= url('formacao') ?>">Petizes &amp; Mini</a></li>
                </ul>
            </div>
            <div>
                <h4 class="footer-heading">Contacto</h4>
                <div class="footer-contact-list">
                    <div class="footer-contact-item">Rua do Desportivo<br>Complexo Desportivo de Vialonga<br>2625-400 Vila Franca de Xira</div>
                    <div class="footer-contact-item">Secretaria: 3.ª a 6.ª, 18h&ndash;20h</div>
                    <div class="footer-contact-item"><a href="mailto:geral@gdvialonga.pt" style="color:rgba(255,255,255,.45)">geral@gdvialonga.pt</a></div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>&copy; <?= date('Y') ?> Grupo Desportivo de Vialonga. Todos os direitos reservados.</p>
            <p>Feito com orgulho em Vialonga</p>
        </div>
    </div>
</footer>
<script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
