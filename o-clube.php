<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle       = 'O Clube';
$pageDescription = 'Historia do GD Vialonga desde 1961 — um clube de bairro que cresceu com a comunidade de Vialonga.';
require_once 'includes/header.php';
?>

<section class="page-hero">
    <div class="container page-hero-inner">
        <nav class="breadcrumb">
            <a href="index.php">Início</a>
            <span class="breadcrumb-sep">/</span>
            <span>O Clube</span>
        </nav>
        <h1>O GDV — um clube de Vialonga</h1>
        <p>Fundado em 1961. Cresceu com a terra e com as pessoas que aqui vivem.</p>
    </div>
</section>

<!-- Numbers strip -->
<div class="club-numbers">
    <div class="club-number-item">
        <div class="club-number-val">1961</div>
        <div class="club-number-label">Ano de fundação</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val blue">412</div>
        <div class="club-number-label">Sócios inscritos</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val">289</div>
        <div class="club-number-label">Atletas esta época</div>
    </div>
    <div class="club-number-item">
        <div class="club-number-val red">7</div>
        <div class="club-number-label">Escalões de formação</div>
    </div>
</div>

<!-- História + Missão -->
<section class="section section-white">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem" class="about-grid">

            <!-- Timeline -->
            <div>
                <span class="section-label">A Nossa História</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.5rem,3vw,2rem);color:var(--gray-900);margin-top:.4rem;margin-bottom:2rem">
                    65 anos de Vialonga
                </h2>
                <div class="history-timeline">
                    <div class="timeline-item">
                        <div class="timeline-year">1961</div>
                        <p class="timeline-text">Tudo começou num café da vila, numa tarde em que um grupo de amigos decidiu que a terra merecia ter o seu próprio clube. Sem grandes recursos, mas com muita vontade, nasceu o <strong>Grupo Desportivo de Vialonga</strong>.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">Anos 70</div>
                        <p class="timeline-text">O clube cresceu com o bairro. As camisolas azuis e brancas tornaram-se conhecidas por toda a região. O campo de jogos era o ponto de encontro ao fim de semana.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">1985</div>
                        <p class="timeline-text">Os seniores chegaram pela primeira vez à <strong>Primeira Divisão Distrital da AF Lisboa</strong>. Na sede, ainda há uma fotografia desse plantel pregada na parede.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">Anos 90</div>
                        <p class="timeline-text">Pais que tinham jogado aqui nos anos 70 começaram a trazer os filhos. O clube passou a ter escalões organizados, treinadores com formação, e cada vez mais famílias ligadas ao GDV.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">2010</div>
                        <p class="timeline-text">Com o apoio da Câmara e da Junta de Freguesia, o Complexo Desportivo foi renovado. Novos balneários, campo sintético e melhores condições para todos.</p>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-year">Hoje</div>
                        <p class="timeline-text">O GDV compete na <strong>1.ª Divisão da AF Lisboa</strong> e continua a ser o lugar onde os miúdos de Vialonga aprendem a jogar à bola — e onde as famílias se encontram.</p>
                    </div>
                </div>
            </div>

            <!-- Missão + Valores — sem linguagem corporativa -->
            <div>
                <span class="section-label">O Que Somos</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.5rem,3vw,2rem);color:var(--gray-900);margin-top:.4rem;margin-bottom:1.75rem">
                    Para que serve o GDV?
                </h2>

                <p style="font-size:1rem;color:var(--gray-600);line-height:1.8;margin-bottom:1.5rem">
                    Para os miúdos que precisam de um lugar para jogar. Para os pais que querem que os filhos façam desporto em boa companhia. Para quem já aqui jogou e quer continuar ligado. Para Vialonga.
                </p>

                <div style="background:var(--gray-50);border-left:4px solid var(--gdv-blue);padding:1.5rem 1.75rem;margin-bottom:1.5rem">
                    <h3 style="font-family:var(--font-display);font-weight:800;font-size:1rem;color:var(--gray-900);margin-bottom:.6rem">O que queremos ser</h3>
                    <p style="color:var(--gray-600);font-size:.9rem;line-height:1.75">
                        Queremos continuar a crescer com Vialonga. Um clube com uma formação de referência no distrito, com uma equipa sénior competitiva, e onde qualquer pessoa da terra se sinta em casa.
                    </p>
                </div>

                <h3 style="font-family:var(--font-display);font-weight:700;font-size:.85rem;color:var(--gray-700);margin-bottom:.85rem">No dia a dia, somos:</h3>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                    <?php foreach(['Respeito pelo adversário','Trabalho nos treinos','Paixão pelo jogo','Família e comunidade','Porta aberta para todos'] as $v): ?>
                    <span style="background:var(--gray-100);border:1px solid var(--gray-200);color:var(--gray-600);padding:.3rem .85rem;font-size:.78rem;font-weight:600;font-family:var(--font-display)"><?= $v ?></span>
                    <?php endforeach; ?>
                </div>

                <!-- Instalações -->
                <div style="margin-top:2.5rem">
                    <h3 style="font-family:var(--font-display);font-weight:800;font-size:1rem;color:var(--gray-900);margin-bottom:1rem;border-bottom:1px solid var(--gray-100);padding-bottom:.5rem">
                        O Complexo Desportivo
                    </h3>
                    <?php
                    $facilities = [
                        ['Campo Principal','Relvado natural de 105×68m com iluminação. Bancadas para ~800 pessoas.'],
                        ['Campo de Treino','Campo sintético onde a formação treina. Aberto todos os dias da semana.'],
                        ['Balneários','Renovados em 2010. Vestiário para visitantes e zona de fisioterapia.'],
                        ['Sede e Bar','Aberto nos dias de jogo e noites de treino — onde as conversas importantes do clube acontecem.'],
                    ];
                    foreach ($facilities as $f): ?>
                    <div style="display:flex;gap:1rem;padding:.75rem 0;border-bottom:1px solid var(--gray-100)">
                        <div style="width:3px;background:var(--gdv-blue);border-radius:2px;flex-shrink:0;margin:3px 0"></div>
                        <div>
                            <strong style="font-family:var(--font-display);font-size:.85rem;color:var(--gray-900)"><?= $f[0] ?></strong>
                            <p style="font-size:.8rem;color:var(--gray-500);margin-top:.2rem;line-height:1.5"><?= $f[1] ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Direção — lista editorial, não grid de cards iguais -->
<section class="section section-gray">
    <div class="container">
        <div style="display:grid;grid-template-columns:260px 1fr;gap:4rem;align-items:start" class="board-layout">
            <div>
                <span class="section-label">As Pessoas</span>
                <h2 style="font-family:var(--font-display);font-weight:900;font-size:clamp(1.3rem,3vw,1.75rem);color:var(--gray-900);margin-top:.4rem;margin-bottom:.85rem">
                    Quem dirige o clube
                </h2>
                <p style="font-size:.88rem;color:var(--gray-500);line-height:1.7">
                    As pessoas que trabalham (muitas vezes em silêncio) para o GDV funcionar todos os dias.
                </p>
            </div>
            <div>
                <?php
                $board = [
                    ['initials'=>'AC','name'=>'António Correia','role'=>'Presidente','note'=>'Sócio desde 1988'],
                    ['initials'=>'MF','name'=>'Manuela Figueira','role'=>'Vice-Presidente','note'=>'Coordenadora de Formação'],
                    ['initials'=>'JS','name'=>'João Severino','role'=>'Secretário-Geral','note'=>'No clube há 22 anos'],
                    ['initials'=>'PC','name'=>'Paulo Cardoso','role'=>'Tesoureiro','note'=>''],
                    ['initials'=>'RL','name'=>'Rita Loureiro','role'=>'Vogal — Formação','note'=>'Mãe de 3 atletas do GDV'],
                    ['initials'=>'NP','name'=>'Nuno Próspero','role'=>'Vogal — Comunicação','note'=>''],
                    ['initials'=>'IS','name'=>'Isaura Soares','role'=>'Presidente da Assembleia Geral','note'=>''],
                    ['initials'=>'LM','name'=>'Luís Montoiro','role'=>'Conselho Fiscal','note'=>''],
                ];
                ?>
                <div class="board-2col">
                    <?php foreach ($board as $m): ?>
                    <div class="board-item">
                        <div class="board-item-initials"><?= $m['initials'] ?></div>
                        <div>
                            <div class="board-item-name"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="board-item-role"><?= htmlspecialchars($m['role']) ?></div>
                            <?php if ($m['note']): ?>
                            <div class="board-item-note"><?= htmlspecialchars($m['note']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
@media (max-width: 900px) {
    .about-grid, .board-layout { grid-template-columns: 1fr !important; gap: 2.5rem !important; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
