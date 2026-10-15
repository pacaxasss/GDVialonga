<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle       = 'Formação — Inscrições';
$pageDescription = 'Inscreva o seu filho no GD Vialonga. Formação de jovens em Vialonga — inscricao gratuita, todos os escaloes.';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="<?= url() ?>">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>Formação</span>
        </nav>
        <h1>Formação GDV</h1>
        <p>Do Sub-7 ao Sub-19. A inscrição é gratuita. Apareça.</p>
    </div>
</section>

<!-- Numbers strip — contexto da formação -->
<div class="club-numbers">
    <div class="club-number-item">
        <div class="club-number-val blue">7</div>
        <div class="club-number-label">Escalões ativos</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val">~230</div>
        <div class="club-number-label">Jovens atletas<br>esta época</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val">12</div>
        <div class="club-number-label">Treinadores com<br>formação UEFA</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val red">65</div>
        <div class="club-number-label">Anos a formar<br>miúdos de Vialonga</div>
    </div>
</div>

<!-- Escalões — lista legível, não grid de cards iguais -->
<section class="section section-white">
    <div class="container">
        <div style="display:grid;grid-template-columns:280px 1fr;gap:4rem;align-items:start" class="escaloes-layout">
            <div>
                <span class="section-label">Os Escalões</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.4rem,3vw,1.9rem);color:var(--gray-900);margin-top:.4rem;margin-bottom:.85rem">
                    Há lugar para todas as idades
                </h2>
                <p style="font-size:.88rem;color:var(--gray-500);line-height:1.7">
                    Se tem entre 5 e 19 anos e vive em Vialonga ou arredores, há um lugar para si aqui.
                    Não é preciso experiência. É preciso aparecer.
                </p>
            </div>
            <div>
                <?php
                $escaloes = [
                    ['Sub-7',  'Petizes',    '5 e 6 anos',  'Primeiro contacto com a bola. Jogos em espaço reduzido, muita brincadeira.', '#fbbf24'],
                    ['Sub-9',  'Mini',       '7 e 8 anos',  'Coordenação, divertimento e as primeiras regras do jogo.', '#34d399'],
                    ['Sub-11', 'Traquinas',  '9 e 10 anos', 'Futebol de 7. Técnica individual e primeiros conceitos táticos.', '#60a5fa'],
                    ['Sub-13', 'Infantis',   '11 e 12 anos','Futebol de 11. Competição distrital na AF Lisboa.', '#f87171'],
                    ['Sub-15', 'Iniciados',  '13 e 14 anos','Nível competitivo mais exigente. Campeonato Distrital AFL.', '#a78bfa'],
                    ['Sub-17', 'Juvenis',    '15 e 16 anos','Futebol de adultos. Preparação para a equipa de Juniores.', '#fb923c'],
                    ['Sub-19', 'Juniores',   '17 e 18 anos','Campeonato Distrital Juniores AF Lisboa.', '#2878BE'],
                ];
                foreach ($escaloes as $e): ?>
                <div style="display:grid;grid-template-columns:auto 1fr;gap:1.25rem;align-items:center;padding:1rem 0;border-bottom:1px solid var(--gray-100)">
                    <div style="text-align:center;min-width:80px">
                        <div style="font-family:var(--font-display);font-weight:900;font-size:1.1rem;color:<?= $e[4] ?>"><?= $e[0] ?></div>
                        <div style="font-family:var(--font-display);font-weight:700;font-size:.75rem;color:var(--gray-900);margin-top:.1rem"><?= $e[1] ?></div>
                        <div style="font-size:.68rem;color:var(--gray-400);margin-top:.1rem"><?= $e[2] ?></div>
                    </div>
                    <p style="font-size:.87rem;color:var(--gray-600);line-height:1.6"><?= $e[3] ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Formulário de Inscrição -->
<section class="section section-gray" id="inscricao">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1.2fr;gap:3rem;align-items:start" class="form-layout">

            <!-- Info lado esquerdo -->
            <div>
                <span class="section-label">Inscrição Gratuita</span>
                <h2 style="font-family:var(--font-display);font-size:clamp(1.5rem,3.5vw,2rem);font-weight:900;color:var(--gray-900);line-height:1.15;margin-top:.4rem;margin-bottom:.85rem">
                    Inscreva o seu filho no GDV.
                </h2>
                <p style="color:var(--gray-600);font-size:.9rem;line-height:1.75;margin-bottom:1.5rem">
                    Preencha o formulário e a nossa coordenadora de formação, a <strong>Sra. Rita</strong>, entra em contacto em 48 horas para confirmar a inscrição e informar sobre horários e treinos.
                </p>
                <p style="color:var(--gray-500);font-size:.85rem;line-height:1.7;margin-bottom:1.75rem">
                    Se preferir fazer a inscrição pessoalmente, passe pela Secretaria do clube nas noites de semana (18h–20h).
                </p>

                <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.75rem">
                    <?php
                    $infos = [
                        ['Local','Complexo Desportivo de Vialonga<br>Rua do Desportivo, Vila Franca de Xira'],
                        ['Email da Formação','<a href="mailto:formacao@gdvialonga.pt" style="color:var(--gdv-blue)">formacao@gdvialonga.pt</a>'],
                        ['Secretaria','3.ª a 6.ª feira: 18h–20h &middot; Sáb: 10h–12h'],
                    ];
                    foreach ($infos as $info): ?>
                    <div style="display:flex;gap:.85rem;align-items:flex-start">
                        <div style="width:3px;background:var(--gdv-blue);border-radius:2px;flex-shrink:0;margin-top:4px;height:1.2em"></div>
                        <div>
                            <div style="font-family:var(--font-display);font-size:.68rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:.08em"><?= $info[0] ?></div>
                            <div style="font-size:.88rem;color:var(--gray-800);margin-top:.15rem;line-height:1.5"><?= $info[1] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="notice-box">
                    A inscrição é <strong>gratuita</strong>. Após confirmação aplica-se uma quota mensal de sócio. Para famílias com dificuldades, fale connosco — há sempre forma de arranjar solução.
                </div>
            </div>

            <!-- Formulário -->
            <div class="form-card">
                <h3 style="font-family:var(--font-display);font-weight:800;font-size:1.1rem;color:var(--gray-900);margin-bottom:.3rem">Formulário de Inscrição</h3>
                <p style="font-size:.78rem;color:var(--gray-400);margin-bottom:1.5rem">Campos com * são obrigatórios.</p>
                <form id="inscricao-form" method="post" action="#inscricao" novalidate>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label" for="nome-atleta">Nome completo do atleta *</label>
                            <input class="form-input" type="text" id="nome-atleta" name="nome_atleta" placeholder="Ex: João Silva" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="data-nasc">Data de nascimento *</label>
                            <input class="form-input" type="date" id="data-nasc" name="data_nascimento" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="escalao">Escalão *</label>
                            <select class="form-select" id="escalao" name="escalao" required>
                                <option value="" disabled selected>Selecionar...</option>
                                <option>Sub-7 Petizes</option>
                                <option>Sub-9 Mini</option>
                                <option>Sub-11 Traquinas</option>
                                <option>Sub-13 Infantis</option>
                                <option>Sub-15 Iniciados</option>
                                <option>Sub-17 Juvenis</option>
                                <option>Sub-19 Juniores</option>
                                <option>Não tenho a certeza — ajudem-me</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label" for="enc-educ">Nome do encarregado *</label>
                            <input class="form-input" type="text" id="enc-educ" name="enc_educacao" placeholder="Ex: Maria Silva" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="tel">Telemóvel *</label>
                            <input class="form-input" type="tel" id="tel" name="telefone" placeholder="9XX XXX XXX" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Email *</label>
                            <input class="form-input" type="email" id="email" name="email" placeholder="exemplo@email.com" required>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label" for="obs">Alguma informação relevante? (opcional)</label>
                            <textarea class="form-textarea" id="obs" name="observacoes" placeholder="Ex: já jogou noutro clube, irmão já inscrito, questão de saúde..."></textarea>
                        </div>
                        <div class="form-group full-width">
                            <button type="submit" class="btn btn-red btn-lg btn-full">Enviar pedido de inscrição</button>
                            <p style="font-size:.7rem;color:var(--gray-400);text-align:center;margin-top:.5rem">Respondemos sempre, nas 48h seguintes.</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 900px) {
    .escaloes-layout, .form-layout { grid-template-columns: 1fr !important; gap: 2rem !important; }
}
</style>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
