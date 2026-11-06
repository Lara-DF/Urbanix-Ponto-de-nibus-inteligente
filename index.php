<?php
require_once 'conexao.php';
require_once 'servicos/ServicoOnibus.php';
require_once 'servicos/ServicoAviso.php';

$servicoOnibus = new ServicoOnibus($pdo);
$servicoAviso = new ServicoAviso($pdo);

// Processar CRUD Ônibus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_onibus'])) {
    $numero = $_POST['numero_linha'];
    $destino = $_POST['destino'];
    $tempo = $_POST['tempo_chegada_minutos'];
    $status = $_POST['status'];

    if (!empty($_POST['id'])) {
        $servicoOnibus->atualizar($_POST['id'], $numero, $destino, $tempo, $status);
    } else {
        $servicoOnibus->criar($numero, $destino, $tempo, $status);
    }
    header("Location: index.php#crud");
    exit;
}

if (isset($_GET['excluir_onibus'])) {
    $servicoOnibus->excluir($_GET['excluir_onibus']);
    header("Location: index.php#crud");
    exit;
}

// Processar CRUD Avisos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao_aviso'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $categoria = $_POST['categoria'];

    if (!empty($_POST['id_aviso'])) {
        $servicoAviso->atualizar($_POST['id_aviso'], $titulo, $descricao, $categoria);
    } else {
        $servicoAviso->criar($titulo, $descricao, $categoria);
    }
    header("Location: index.php#crud");
    exit;
}

if (isset($_GET['excluir_aviso'])) {
    $servicoAviso->excluir($_GET['excluir_aviso']);
    header("Location: index.php#crud");
    exit;
}

$linhas = $servicoOnibus->listarTodos();
$avisos = $servicoAviso->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URBANIX - Smart Totem Urbano</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #030712;
            --surface: #0b1329;
            --surface-hover: #111c3a;
            --border: #1e293b;
            --border-glow: #38bdf844;
            --primary: #38bdf8;
            --primary-glow: #38bdf888;
            --accent: #818cf8;
            --text-title: #f8fafc;
            --text-body: #94a3b8;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg); color: var(--text-title); overflow-x: hidden; scroll-behavior: smooth; }

        .glow-bg {
            position: fixed; top: -10%; left: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
            z-index: -1; pointer-events: none;
        }
        .glow-bg-2 {
            position: fixed; bottom: -10%; right: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(129, 140, 248, 0.12) 0%, rgba(3, 7, 18, 0) 70%);
            z-index: -1; pointer-events: none;
        }

        header {
            position: sticky; top: 0; z-index: 100;
            background: rgba(3, 7, 18, 0.85); backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 1.2rem 3rem; display: flex; justify-content: space-between; align-items: center;
        }

        .logo { font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; color: #fff; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .logo span { color: var(--primary); text-shadow: 0 0 12px var(--primary-glow); }

        nav { display: flex; gap: 2rem; align-items: center; }
        nav a { color: var(--text-body); text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: all 0.2s; }
        nav a:hover { color: var(--primary); text-shadow: 0 0 8px var(--primary-glow); }

        .btn-tts {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.1), rgba(129, 140, 248, 0.1));
            border: 1px solid var(--primary); color: #fff; padding: 0.6rem 1.2rem;
            border-radius: 30px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.2); transition: all 0.3s;
        }
        .btn-tts:hover { transform: translateY(-2px); box-shadow: 0 0 25px var(--primary-glow); background: var(--primary); color: #000; }

        .hero { display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 3rem; max-width: 1350px; margin: 0 auto; padding: 4rem 2rem; align-items: center; }
        .badge-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); color: var(--primary); padding: 6px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; text-transform: uppercase; }
        
        .hero-title { font-size: 3.5rem; line-height: 1.1; font-weight: 800; margin-bottom: 1.2rem; }
        .hero-title .gradient-text { background: linear-gradient(135deg, #38bdf8 0%, #818cf8 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-desc { color: var(--text-body); font-size: 1.15rem; line-height: 1.7; margin-bottom: 2.2rem; }

        .btn-group { display: flex; gap: 1.2rem; }
        .btn-primary { background: linear-gradient(135deg, #0284c7, #38bdf8); color: #fff; padding: 0.9rem 1.8rem; border-radius: 10px; text-decoration: none; font-weight: 700; box-shadow: 0 0 25px rgba(56, 189, 248, 0.4); transition: all 0.3s; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 0 35px rgba(56, 189, 248, 0.6); }
        .btn-secondary { background: var(--surface); border: 1px solid var(--border); color: #fff; padding: 0.9rem 1.8rem; border-radius: 10px; text-decoration: none; font-weight: 700; transition: all 0.3s; }
        .btn-secondary:hover { border-color: var(--primary); background: var(--surface-hover); }

        .canvas-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: 24px; padding: 12px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7); position: relative; overflow: hidden;
        }
        .canvas-container { width: 100%; height: 450px; border-radius: 16px; position: relative; overflow: hidden; background: radial-gradient(circle at center, #0b172a 0%, #030712 100%); }
        #canvas3d { width: 100%; height: 100%; cursor: grab; }
        #canvas3d:active { cursor: grabbing; }

        .section-container { max-width: 1350px; margin: 0 auto; padding: 4rem 2rem; }
        .section-title { text-align: center; margin-bottom: 3.5rem; }
        .section-title h2 { font-size: 2.5rem; font-weight: 800; margin-bottom: 0.8rem; }
        .section-title p { color: var(--text-body); font-size: 1.1rem; }

        .specs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        .spec-card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 2.2rem; transition: transform 0.3s; }
        .spec-card:hover { transform: translateY(-5px); border-color: var(--primary); }
        .spec-icon { font-size: 2.5rem; margin-bottom: 1.2rem; }
        .spec-card h3 { font-size: 1.4rem; font-weight: 700; margin-bottom: 1rem; color: #fff; }
        .spec-card p { color: var(--text-body); line-height: 1.6; font-size: 0.95rem; margin-bottom: 1rem; }
        .spec-tag { display: inline-block; background: rgba(56, 189, 248, 0.1); color: var(--primary); padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; margin-top: 0.5rem; }

        .totem-display-sim {
            background: #020617; border: 2px solid var(--primary); border-radius: 20px; padding: 2.5rem;
            box-shadow: 0 0 35px rgba(56, 189, 248, 0.25); margin-bottom: 4rem;
        }
        .display-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed var(--border); padding-bottom: 1.2rem; margin-bottom: 1.8rem; }
        .display-live { display: flex; align-items: center; gap: 8px; color: var(--success); font-weight: 700; font-size: 0.9rem; }
        .pulse-dot { width: 10px; height: 10px; background: var(--success); border-radius: 50%; box-shadow: 0 0 10px var(--success); animation: pulse 1.5s infinite; }
        @keyframes pulse { 0% { opacity: 0.4; } 50% { opacity: 1; } 100% { opacity: 0.4; } }

        .display-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .display-box { background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border); border-radius: 14px; padding: 1.5rem; }
        .display-box h4 { color: var(--primary); font-size: 1.2rem; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 8px; }

        .crud-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.5rem; }
        .crud-card { background: var(--surface); border: 1px solid var(--border); border-radius: 20px; padding: 2.2rem; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .crud-card h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: #fff; display: flex; align-items: center; gap: 10px; }

        form label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-body); margin-bottom: 6px; }
        form input, form select, form textarea {
            width: 100%; padding: 0.85rem 1rem; background: #030712; border: 1px solid var(--border);
            color: #fff; border-radius: 8px; margin-bottom: 1.2rem; font-size: 0.95rem; transition: border 0.2s;
        }
        form input:focus, form select:focus, form textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 10px var(--primary-glow); }
        
        form button {
            width: 100%; padding: 0.95rem; background: linear-gradient(135deg, #0284c7, #38bdf8); color: #fff;
            border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 1rem; transition: all 0.2s;
        }
        form button:hover { opacity: 0.9; transform: translateY(-1px); }

        .item-list { margin-top: 1.8rem; display: flex; flex-direction: column; gap: 1rem; }
        .item-row {
            background: #030712; border: 1px solid var(--border); padding: 1.2rem; border-radius: 12px;
            display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;
        }
        .item-row:hover { border-color: var(--border-glow); transform: translateX(3px); }
        .btn-del { background: rgba(239, 68, 68, 0.15); border: 1px solid var(--danger); color: var(--danger); padding: 0.4rem 0.9rem; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 700; transition: all 0.2s; }
        .btn-del:hover { background: var(--danger); color: white; }

        @media (max-width: 960px) {
            .hero, .crud-grid, .display-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 2.6rem; }
        }
    </style>
</head>
<body>

    <div class="glow-bg"></div>
    <div class="glow-bg-2"></div>

    <header>
        <a href="#" class="logo"><span>URBANIX</span></a>
        <nav>
            <a href="#projeto">Projeto</a>
            <a href="#detalhes">Como Funciona</a>
            <a href="#painel-vivo">Display Ao Vivo</a>
            <a href="#crud">Painel</a>
        </nav>
        <button class="btn-tts" onclick="falarTexto('Ponto de Ônibus Inteligente, URBANIX. Conectividade, acessibilidade e informação em tempo real para a cidade.')">🔊 Ouvir Resumo</button>
    </header>

    <section class="hero" id="projeto">
        <div>
            <div class="badge-tag">⚡ Feira Tecnológica</div>
            <h1 class="hero-title">Ponto de Ônibus! <br><span class="gradient-text">Inteligente & Acessível</span></h1>
            <p class="hero-desc">A <strong>URBANIX</strong> transforma pontos de ônibus em centrais de tecnologia. Nós conectamos informações sobre os ônibus, telas inteligentes e dados que funcionam mesmo sem internet. Assim, garantimos que todo cidadão consiga se locomover pela cidade com facilidade e autonomia.</p>
            <div class="btn-group">
                <a href="#crud" class="btn-primary">Acessar Painel</a>
                <a href="#detalhes" class="btn-secondary">Definição do Projeto</a>
            </div>
        </div>
        
        <!-- Canvas do Modelo 3D Detalhado idêntico à imagem -->
        <div class="canvas-card">
            <div class="canvas-container">
                <div id="canvas3d"></div>
            </div>
            <div style="text-align: center; margin-top: 8px; font-size: 0.8rem; color: var(--text-body);">🖱️ Clique e arraste para girar o abrigo e o totem 3D</div>
        </div>
    </section>

    <!-- SECÇÃO DETALHADA COM AS ESPECIFICAÇÕES DO PROJETO -->
    <section class="section-container" id="detalhes">
        <div class="section-title">
            <h2>Definição e Arquitetura da URBANIX</h2>
            <p>Entenda como o protótipo processa informações online e mantém dados offline seguros</p>
        </div>

        <div class="specs-grid">
            <div class="spec-card">
                <div class="spec-icon">🚌</div>
                <h3>1. Informações de Ônibus (API)</h3>
                <p>Para exibir horários reais como <em>"Linha 273 — Terminal Central — chegada em 5 minutos"</em>, o sistema integra-se a fontes de dados de transporte público atualizadas pela internet.</p>
                <div class="spec-tag">Em Tempo Real</div>
            </div>

            <div class="spec-card">
                <div class="spec-icon">💻</div>
                <h3>2. Arquitetura de Conexão</h3>
                <p>Oferece duas abordagens: <strong>Computador Intermediário</strong> (ideal para demonstrações onde o PC interage com a API e o Arduino) e <strong>ESP32 com Wi-Fi</strong> (versão definitiva onde o microcontrolador busca os dados direto da internet).</p>
                <div class="spec-tag">IoT / ESP32</div>
            </div>

            <div class="spec-card">
                <div class="spec-icon">🌐​</div>
                <h3>3. Informações Offline</h3>
                <p>Mesmo sem conexão com a internet, o URBANIX continua funcional! O sistema armazena previamente dados vitais como campanhas de vacinação, locais acessíveis próximos, avisos e orientações aos passageiros.</p>
                <div class="spec-tag">Alta Disponibilidade</div>
            </div>
        </div>
    </section>

    <section class="section-container" id="painel-vivo">
        <div class="section-title">
            <h2>Simulação do Display do Totem</h2>
            <p>Esta é a interface exibida na tela digital do Totem URBANIX instalado na rua</p>
        </div>

        <div class="totem-display-sim">
            <div class="display-header">
                <div>
                    <h3 style="color:#fff;">URBANIX - Estação Central</h3>
                    <small style="color:var(--text-body);">São Paulo, SP • Sincronizado com Banco de Dados</small>
                </div>
                <div class="display-live">
                    <div class="pulse-dot"></div> SISTEMA ONLINE
                </div>
            </div>

            <div class="display-grid">
                <div class="display-box">
                    <h4>🚌 Próximos Ônibus (API / Online)</h4>
                    <?php if(empty($linhas)): ?>
                        <p style="color:var(--text-body);">Nenhum horário cadastrado no momento.</p>
                    <?php else: ?>
                        <?php foreach(array_slice($linhas, 0, 4) as $l): ?>
                            <div style="display:flex; justify-content:space-between; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #1e293b;">
                                <div>
                                    <strong style="color:#fff;">Linha <?= htmlspecialchars($l['numero_linha']) ?></strong> - <?= htmlspecialchars($l['destino']) ?>
                                </div>
                                <span style="color:var(--primary); font-weight:bold;">⏱️ <?= htmlspecialchars($l['tempo_chegada_minutos']) ?> min</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="display-box">
                    <h4>🟡 Avisos & Utilidade Pública (Offline/Sistema)</h4>
                    <?php if(empty($avisos)): ?>
                        <p style="color:var(--text-body);">Nenhum comunicado ativo.</p>
                    <?php else: ?>
                        <?php foreach(array_slice($avisos, 0, 3) as $a): ?>
                            <div style="margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #1e293b;">
                                <span style="background:rgba(56,189,248,0.2); color:var(--primary); padding:2px 6px; border-radius:4px; font-size:0.75rem; font-weight:bold;"><?= htmlspecialchars($a['categoria']) ?></span>
                                <strong style="color:#fff; display:block; margin-top:4px;"><?= htmlspecialchars($a['titulo']) ?></strong>
                                <small style="color:var(--text-body);"><?= htmlspecialchars($a['descricao']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="section-container" id="crud">
        <div class="section-title">
            <h2>Painel Administrativo</h2>
            <p>Gerencie as entidades conectadas ao banco de dados</p>
        </div>

        <div class="crud-grid">
            
            <!-- CRUD ONIBUS -->
            <div class="crud-card">
                <h3>🚌 Gestão de Ônibus</h3>
                <form method="POST">
                    <input type="hidden" name="acao_onibus" value="1">
                    <div>
                        <label>Número da Linha</label>
                        <input type="text" name="numero_linha" placeholder="Ex: 273" required>
                    </div>
                    <div>
                        <label>Destino Principal</label>
                        <input type="text" name="destino" placeholder="Ex: Terminal Central" required>
                    </div>
                    <div>
                        <label>Chegada Estimada (minutos)</label>
                        <input type="number" name="tempo_chegada_minutos" placeholder="Ex: 5" required>
                    </div>
                    <div>
                        <label>Status da Linha</label>
                        <select name="status">
                            <option value="No horário">No horário</option>
                            <option value="Atrasado">Atrasado</option>
                            <option value="Em Manutenção">Em Manutenção</option>
                        </select>
                    </div>
                    <button type="submit">Cadastrar / Salvar Linha</button>
                </form>

                <div class="item-list">
                    <?php foreach($linhas as $l): ?>
                        <div class="item-row">
                            <div>
                                <strong style="color:#fff;">Linha <?= htmlspecialchars($l['numero_linha']) ?></strong> → <?= htmlspecialchars($l['destino']) ?>
                                <div style="font-size:0.85rem; color:var(--primary); margin-top:4px;">⏱️ <?= htmlspecialchars($l['tempo_chegada_minutos']) ?> min (<?= htmlspecialchars($l['status']) ?>)</div>
                            </div>
                            <a href="?excluir_onibus=<?= $l['id'] ?>" class="btn-del" onclick="return confirm('Remover esta linha?')">Remover</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CRUD AVISOS -->
            <div class="crud-card">
                <h3>📢 Alertas & Serviços</h3>
                <form method="POST">
                    <input type="hidden" name="acao_aviso" value="1">
                    <div>
                        <label>Título do Comunicado</label>
                        <input type="text" name="titulo" placeholder="Ex: Campanha de Vacinação" required>
                    </div>
                    <div>
                        <label>Categoria</label>
                        <input type="text" name="categoria" placeholder="Ex: Saúde, Acessibilidade, Avisos" required>
                    </div>
                    <div>
                        <label>Descrição Completa</label>
                        <textarea name="descricao" placeholder="Detalhes do comunicado..." rows="3" required></textarea>
                    </div>
                    <button type="submit">Cadastrar / Salvar Alerta</button>
                </form>

                <div class="item-list">
                    <?php foreach($avisos as $a): ?>
                        <div class="item-row">
                            <div>
                                <span style="color:var(--accent); font-size:0.8rem; font-weight:bold;">[<?= htmlspecialchars($a['categoria']) ?>]</span>
                                <strong style="color:#fff; display:block;"><?= htmlspecialchars($a['titulo']) ?></strong>
                                <div style="font-size:0.85rem; color:var(--text-body);"><?= htmlspecialchars($a['descricao']) ?></div>
                            </div>
                            <a href="?excluir_aviso=<?= $a['id'] ?>" class="btn-del" onclick="return confirm('Remover este aviso?')">Remover</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </section>

    <script>
        function falarTexto(texto) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel();
                const msg = new SpeechSynthesisUtterance(texto);
                msg.lang = 'pt-BR';
                window.speechSynthesis.speak(msg);
            }
        }

        // Script 3D Detalhado (Abrigo, Banco de Madeira, Teto LED e Totem)
        const container = document.getElementById('canvas3d');
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(40, container.clientWidth / container.clientHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        const controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;

        // Luzes Ambientais e Direcionais de Estúdio
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);
        
        const dirLight = new THREE.DirectionalLight(0x38bdf8, 1.2);
        dirLight.position.set(5, 8, 5);
        scene.add(dirLight);

        const group = new THREE.Group();

        // 1. Base / Piso do Abrigo
        const baseGeo = new THREE.BoxGeometry(6.2, 0.15, 3.2);
        const baseMat = new THREE.MeshStandardMaterial({ color: 0x1e293b, roughness: 0.5 });
        const base = new THREE.Mesh(baseGeo, baseMat);
        group.add(base);

        // 2. Cobertura Superior Preta com Moldura
        const roofGeo = new THREE.BoxGeometry(6.4, 0.25, 3.4);
        const roofMat = new THREE.MeshStandardMaterial({ color: 0x090d16, roughness: 0.3 });
        const roof = new THREE.Mesh(roofGeo, roofMat);
        roof.position.set(0, 2.6, 0);
        group.add(roof);

        // 3. Iluminação LED Embutida no Teto
        const ledGeo = new THREE.BoxGeometry(5.8, 0.05, 0.1);
        const ledMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
        const led = new THREE.Mesh(ledGeo, ledMat);
        led.position.set(0, 2.45, 0);
        group.add(led);

        // 4. Colunas Estruturais Metálicas
        const colGeo = new THREE.BoxGeometry(0.12, 2.5, 0.12);
        const colMat = new THREE.MeshStandardMaterial({ color: 0x111827, metalness: 0.8 });
        
        const col1 = new THREE.Mesh(colGeo, colMat); col1.position.set(-3.1, 1.25, -1.5); group.add(col1);
        const col2 = new THREE.Mesh(colGeo, colMat); col2.position.set(3.1, 1.25, -1.5); group.add(col2);
        const col3 = new THREE.Mesh(colGeo, colMat); col3.position.set(-3.1, 1.25, 1.5); group.add(col3);

        // 5. Painéis de Vidro Traseiros
        const glassGeo = new THREE.BoxGeometry(5.9, 2.4, 0.05);
        const glassMat = new THREE.MeshPhysicalMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.25, roughness: 0.1, metalness: 0.1 });
        const glass = new THREE.Mesh(glassGeo, glassMat);
        glass.position.set(0, 1.25, -1.48);
        group.add(glass);

        // 6. Banco de Madeira Interno (Ripado)
        const seatMat = new THREE.MeshStandardMaterial({ color: 0xa16207, roughness: 0.6 }); // Cor madeira
        const supportMat = new THREE.MeshStandardMaterial({ color: 0x111827 });
        
        // Assento
        const plankGeo = new THREE.BoxGeometry(3.5, 0.08, 0.45);
        const seat = new THREE.Mesh(plankGeo, seatMat);
        seat.position.set(-0.8, 0.9, -1.1);
        group.add(seat);

        // Encosto do banco
        const backGeo = new THREE.BoxGeometry(3.5, 0.6, 0.08);
        const back = new THREE.Mesh(backGeo, seatMat);
        back.position.set(-0.8, 1.3, -1.35);
        group.add(back);

        // Pés do banco
        const legGeo = new THREE.BoxGeometry(0.1, 0.9, 0.1);
        const leg1 = new THREE.Mesh(legGeo, supportMat); leg1.position.set(-2.2, 0.45, -1.1); group.add(leg1);
        const leg2 = new THREE.Mesh(legGeo, supportMat); leg2.position.set(0.6, 0.45, -1.1); group.add(leg2);

        // 7. Totem Urbanix (Lateral Direita com Ecrã e Botões)
        const totemGeo = new THREE.BoxGeometry(1.2, 2.5, 0.6);
        const totemMat = new THREE.MeshStandardMaterial({ color: 0x0f172a, roughness: 0.2 });
        const totem = new THREE.Mesh(totemGeo, totemMat);
        totem.position.set(2.4, 1.25, 0.2);
        group.add(totem);

        // Ecrã Digital do Totem
        const screenGeo = new THREE.PlaneGeometry(0.9, 1.3);
        const screenMat = new THREE.MeshBasicMaterial({ color: 0x0284c7 });
        const screen = new THREE.Mesh(screenGeo, screenMat);
        screen.position.set(2.4, 1.55, 0.51);
        group.add(screen);

        // Botões de Acessibilidade (Cores: Azul, Amarelo, Vermelho SOS)
        const btnGeo = new THREE.CylinderGeometry(0.06, 0.06, 0.04, 32);
        btnGeo.rotateX(Math.PI / 2);
        
        const btnBlueMat = new THREE.MeshBasicMaterial({ color: 0x38bdf8 });
        const btnYellowMat = new THREE.MeshBasicMaterial({ color: 0xfacc15 });
        const btnRedMat = new THREE.MeshBasicMaterial({ color: 0xef4444 });

        const btn1 = new THREE.Mesh(btnGeo, btnBlueMat); btn1.position.set(2.1, 0.75, 0.51); group.add(btn1);
        const btn2 = new THREE.Mesh(btnGeo, btnYellowMat); btn2.position.set(2.4, 0.75, 0.51); group.add(btn2);
        const btn3 = new THREE.Mesh(btnGeo, btnRedMat); btn3.position.set(2.7, 0.75, 0.51); group.add(btn3);

        scene.add(group);
        camera.position.set(5, 3.5, 6.5);
        controls.update();

        function animate() {
            requestAnimationFrame(animate);
            group.rotation.y += 0.0015; // Rotação bem suave para apreciar todos os ângulos
            controls.update();
            renderer.render(scene, camera);
        }
        animate();

        window.addEventListener('resize', () => {
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        });
    </script>
</body>
</html>