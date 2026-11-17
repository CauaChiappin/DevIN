/**
 * DevIN Arcade — oito minijogos adicionais.
 * Cada factory respeita o contrato do motor existente: game, level, score,
 * key(), pointer(), update() e draw(). Nenhum jogo executa SQL; o progresso
 * continua sendo salvo pela API autenticada do arcade original.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const metadata = { // Explicação DevIN: Declara variável JavaScript metadata para reutilizar valor ou resultado.
        pong: { title: 'Pong Solo', description: 'Rebata a bola na plataforma e sobreviva às velocidades crescentes.', hint: 'A/D, setas ou toque para mover a plataforma.', genre: 'Habilidade' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        dodge: { title: 'Chuva de Meteoros', description: 'Desvie dos meteoros e descubra até onde consegue resistir.', hint: 'A/D, setas ou arraste para desviar.', genre: 'Reflexos' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        flappy: { title: 'Voo Pixel', description: 'Atravesse as aberturas sem tocar nos canos.', hint: 'Espaço, seta para cima ou toque para bater as asas.', genre: 'Arcade' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        target: { title: 'Alvo Rápido', description: 'Clique nos círculos antes que o tempo acabe para acumular pontos.', hint: 'Toque nos alvos ou clique com o mouse.', genre: 'Reflexos' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        collector: { title: 'Caça às Estrelas', description: 'Colete estrelas e evite as formas vermelhas.', hint: 'Setas, WASD ou toque na posição desejada.', genre: 'Aventura' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        racer: { title: 'Desvio Turbo', description: 'Mude de faixa e ultrapasse os carros sem colisões.', hint: 'A/D, setas ou toque à esquerda/direita.', genre: 'Corrida' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        simon: { title: 'Sequência de Cores', description: 'Memorize e repita uma sequência de quatro quadrantes.', hint: 'Toque nos quadrantes ou use as teclas 1 a 4.', genre: 'Raciocínio' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        basket: { title: 'Cesta Relâmpago', description: 'Mova a cesta para recolher as bolas que caem.', hint: 'A/D, setas ou arraste para os lados.', genre: 'Habilidade' } // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function create(context) { // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
        const { ctx, canvas, colors, gameOver, saveProgress } = context; // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        const width = 640, height = 480; // Explicação DevIN: Declara variável JavaScript width para reutilizar valor ou resultado.
        const clamp = (n, lo, hi) => Math.max(lo, Math.min(hi, n)); // Explicação DevIN: Declara variável JavaScript clamp para reutilizar valor ou resultado.
        const rand = (lo, hi) => lo + Math.random() * (hi - lo); // Explicação DevIN: Declara variável JavaScript rand para reutilizar valor ou resultado.
        const px = (event) => { // Explicação DevIN: Declara variável JavaScript px para reutilizar valor ou resultado.
            const rect = canvas.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
            return (event.clientX - rect.left) / Math.max(1, rect.width) * width; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const point = (event) => { // Explicação DevIN: Declara variável JavaScript point para reutilizar valor ou resultado.
            const rect = canvas.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                x: (event.clientX - rect.left) / Math.max(1, rect.width) * width, // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
                y: (event.clientY - rect.top) / Math.max(1, rect.height) * height // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const background = () => { // Explicação DevIN: Declara variável JavaScript background para reutilizar valor ou resultado.
            ctx.fillStyle = '#061c39'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ctx.fillRect(0, 0, width, height); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            ctx.strokeStyle = 'rgba(134,190,255,.10)'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            for (let x = 0; x < width; x += 40) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, height); ctx.stroke(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            for (let y = 0; y < height; y += 40) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(width, y); ctx.stroke(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const write = (s, x = 20, y = 32, size = 20) => { // Explicação DevIN: Declara variável JavaScript write para reutilizar valor ou resultado.
            ctx.fillStyle = '#f5faff'; ctx.font = '700 ' + size + 'px system-ui, sans-serif'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ctx.textAlign = 'left'; ctx.fillText(String(s), x, y); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const circle = (x, y, r, color) => { // Explicação DevIN: Declara variável JavaScript circle para reutilizar valor ou resultado.
            ctx.beginPath(); ctx.arc(x, y, r, 0, Math.PI * 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            ctx.fillStyle = color; ctx.fill(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const bar = (x,y,w,h,c) => { ctx.fillStyle=c;ctx.fillRect(x,y,w,h); }; // Explicação DevIN: Declara variável JavaScript bar para reutilizar valor ou resultado.
        const levelUp = (engine, increment) => { // Explicação DevIN: Declara variável JavaScript levelUp para reutilizar valor ou resultado.
            const next = 1 + Math.floor(engine.score / increment); // Explicação DevIN: Declara variável JavaScript next para reutilizar valor ou resultado.
            if (next > engine.level) { engine.level = next; saveProgress(engine); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Apenas uma instância do motor está ativa. gameOver() pertence ao portal
        // e já salva recordes, mostra classificação e impede atualizações extras.
        const finish = (engine) => { if (!engine.ended) gameOver(); }; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        function pongEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método pongEngine, com seus parâmetros e comportamento.
            const s = { x: 320, ballX: 320, ballY: 260, vx: 225, vy: -270, streak: 0 }; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'pong', level, score, state:s, ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    if (['ArrowLeft','KeyA'].includes(key)) s.x = clamp(s.x - 46, 56, 584); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (['ArrowRight','KeyD'].includes(key)) s.x = clamp(s.x + 46, 56, 584); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event) { s.x = clamp(px(event),56,584); }, // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    const d = Math.min(delta, 50) / 1000; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                    s.ballX += s.vx * d; s.ballY += s.vy * d; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (s.ballX < 13 || s.ballX > 627) { s.ballX=clamp(s.ballX,13,627); s.vx *= -1; } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (s.ballY < 12) { s.ballY=12; s.vy=Math.abs(s.vy); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (s.ballY > 423 && s.ballY < 455 && s.vy > 0 && Math.abs(s.ballX-s.x) < 65) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        s.vy = -Math.min(620, Math.abs(s.vy) + 12); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        s.vx = clamp((s.ballX-s.x)*5, -380,380); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        s.streak++; this.score += 25; levelUp(this, 125); // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    if (s.ballY > 494) finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    background(); bar(s.x-58,445,116,14,'#53cce7'); // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    circle(s.ballX,s.ballY,11,'#ffd15b'); // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                    write('Rebatidas: '+s.streak,20,34); write('Não deixe a bola cair!',20,462,14); // Explicação DevIN: Continua expressão ou bloco pertencente a função pongEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function dodgeEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método dodgeEngine, com seus parâmetros e comportamento.
            const s={x:320,objects:[],spawn:0,travel:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'dodge',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                key(key){ // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    if(['ArrowLeft','KeyA'].includes(key))s.x=clamp(s.x-40,22,618); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(['ArrowRight','KeyD'].includes(key))s.x=clamp(s.x+40,22,618); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event){s.x=clamp(px(event),22,618);}, // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    const d=Math.min(delta,60)/1000; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                    s.travel+=d;this.score=Math.floor(s.travel*15); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    s.spawn-=delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.spawn<=0){s.objects.push({x:rand(22,618),y:-20,r:rand(12,24),speed:rand(155,245)+this.level*14});s.spawn=Math.max(210,700-this.level*32);} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    s.objects.forEach(o=>o.y+=o.speed*d); // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    s.objects=s.objects.filter(o=>o.y<520); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.objects.some(o=>Math.hypot(s.x-o.x,430-o.y)<o.r+17))finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    levelUp(this,150); // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    background();s.objects.forEach(o=>circle(o.x,o.y,o.r,'#f36d7b')); // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    circle(s.x,430,18,'#58d9e7');circle(s.x,430,7,'#ffffff'); // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                    write('Sobreviva à chuva!',20,32); // Explicação DevIN: Continua expressão ou bloco pertencente a função dodgeEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function flappyEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método flappyEngine, com seus parâmetros e comportamento.
            const s={y:220,vy:0,pipes:[],timer:0,spawn:600}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            const flap=()=>{s.vy=-260;}; // Explicação DevIN: Declara variável JavaScript flap para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'flappy',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                key(key){if(['Space','ArrowUp','KeyW'].includes(key))flap();}, // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                pointer(){flap();}, // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                    const d=Math.min(delta,50)/1000; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                    s.vy+=650*d;s.y+=s.vy*d; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    s.spawn-=delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.spawn<=0){s.pipes.push({x:665,gap:rand(145,325),scored:false});s.spawn=1550;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    for(const p of s.pipes){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        p.x-=(160+this.level*8)*d; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if(!p.scored&&p.x+58<150){p.scored=true;this.score+=30;levelUp(this,120);} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        if(150+16>p.x&&150-16<p.x+58&&(s.y-16<p.gap-74||s.y+16>p.gap+74))finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    s.pipes=s.pipes.filter(p=>p.x>-65); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.y<15||s.y>465)finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                    background(); // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                    s.pipes.forEach(p=>{bar(p.x,0,58,p.gap-74,'#43caab');bar(p.x,p.gap+74,58,height-(p.gap+74),'#43caab');}); // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                    circle(150,s.y,16,'#ffd35c');circle(156,s.y-4,4,'#12395d'); // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                    write('Toque para voar',20,33); // Explicação DevIN: Continua expressão ou bloco pertencente a função flappyEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function targetEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método targetEngine, com seus parâmetros e comportamento.
            const s={remaining:45,target:{x:320,y:230,r:37},hit:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            const newTarget=()=>{s.target={x:rand(60,580),y:rand(90,415),r:Math.max(16,40-level*2)};}; // Explicação DevIN: Declara variável JavaScript newTarget para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'target',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                key(){}, // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                pointer(event){ // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    const p=point(event); // Explicação DevIN: Declara variável JavaScript p para reutilizar valor ou resultado.
                    if(Math.hypot(p.x-s.target.x,p.y-s.target.y)<=s.target.r){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        s.hit++;this.score+=20;levelUp(this,140);newTarget(); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                update(delta){s.remaining=Math.max(0,s.remaining-delta/1000);if(s.remaining<=0)finish(this);}, // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    background();circle(s.target.x,s.target.y,s.target.r+9,'#6ab4f5'); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    circle(s.target.x,s.target.y,s.target.r,'#ffd35c'); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    circle(s.target.x,s.target.y,s.target.r*.45,'#e74f61'); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    write('Tempo: '+Math.ceil(s.remaining)+'s',20,33); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                    write('Acertos: '+s.hit,460,33); // Explicação DevIN: Continua expressão ou bloco pertencente a função targetEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function collectorEngine(level=1,score=0){ // Explicação DevIN: Declara função/método collectorEngine, com seus parâmetros e comportamento.
            const s={x:320,y:240,stars:[],hazards:[],time:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            const make=(bad=false)=>({x:rand(25,615),y:rand(65,450),bad}); // Explicação DevIN: Declara variável JavaScript make para reutilizar valor ou resultado.
            s.stars=[make(),make(),make()]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            s.hazards=[make(true)]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'collector',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                key(key){ // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    const k={ArrowLeft:[-30,0],KeyA:[-30,0],ArrowRight:[30,0],KeyD:[30,0],ArrowUp:[0,-30],KeyW:[0,-30],ArrowDown:[0,30],KeyS:[0,30]}[key]; // Explicação DevIN: Declara variável JavaScript k para reutilizar valor ou resultado.
                    if(k){s.x=clamp(s.x+k[0],14,626);s.y=clamp(s.y+k[1],52,466);} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event){const p=point(event);s.x=clamp(p.x,14,626);s.y=clamp(p.y,52,466);}, // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    s.time+=delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    for(let i=0;i<s.stars.length;i++){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        if(Math.hypot(s.x-s.stars[i].x,s.y-s.stars[i].y)<26){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            s.stars[i]=make();this.score+=25;levelUp(this,150); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            if(this.score%150===0&&s.hazards.length<7)s.hazards.push(make(true)); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    for(const h of s.hazards){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        const d=Math.hypot(s.x-h.x,s.y-h.y)||1; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                        h.x+=(s.x-h.x)/d*delta*.012*(1+this.level*.10); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        h.y+=(s.y-h.y)/d*delta*.012*(1+this.level*.10); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if(d<23)finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    background(); // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    s.stars.forEach(p=>{circle(p.x,p.y,12,'#ffd15b');circle(p.x,p.y,4,'#fff');}); // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    s.hazards.forEach(p=>circle(p.x,p.y,15,'#fa6977')); // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                    circle(s.x,s.y,16,'#55ddc8');write('Colete as estrelas • evite o vermelho',20,33,17); // Explicação DevIN: Continua expressão ou bloco pertencente a função collectorEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function racerEngine(level=1,score=0){ // Explicação DevIN: Declara função/método racerEngine, com seus parâmetros e comportamento.
            const lanes=[180,320,460]; // Explicação DevIN: Declara variável JavaScript lanes para reutilizar valor ou resultado.
            const s={lane:1,cars:[],distance:0,spawn:300,line:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'racer',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                key(key){ // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    if(['ArrowLeft','KeyA'].includes(key))s.lane=clamp(s.lane-1,0,2); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(['ArrowRight','KeyD'].includes(key))s.lane=clamp(s.lane+1,0,2); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event){const x=px(event);s.lane=x<250?0:x>390?2:1;}, // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    const d=Math.min(delta,60)/1000; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                    s.distance+=d*(70+this.level*8);this.score=Math.floor(s.distance); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    s.line=(s.line+delta*.32)%70;s.spawn-=delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.spawn<=0){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        s.cars.push({lane:Math.floor(Math.random()*3),y:-90,passed:false}); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                        s.spawn=Math.max(420,1150-this.level*42); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    for(const c of s.cars){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        c.y+=(240+this.level*16)*d; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if(c.lane===s.lane&&c.y>347&&c.y<440)finish(this); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    s.cars=s.cars.filter(c=>c.y<520); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    levelUp(this,200); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    background();bar(105,0,430,480,'#163b64'); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    ctx.fillStyle='#e6f2ff'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    for(let x of [250,390])for(let y=-70+s.line;y<480;y+=70)ctx.fillRect(x-2,y,4,38); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    s.cars.forEach(c=>{bar(lanes[c.lane]-25,c.y,50,72,'#f16b7c');bar(lanes[c.lane]-17,c.y+12,34,18,'#752d48');}); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    bar(lanes[s.lane]-25,378,50,72,'#57d5e7');bar(lanes[s.lane]-17,390,34,18,'#1c6c86'); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                    write('Distância: '+this.score,20,33); // Explicação DevIN: Continua expressão ou bloco pertencente a função racerEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function simonEngine(level=1,score=0){ // Explicação DevIN: Declara função/método simonEngine, com seus parâmetros e comportamento.
            const pads=[{x:106,y:100,color:'#5ecaf5'},{x:326,y:100,color:'#ffd35c'},{x:106,y:260,color:'#69d8b0'},{x:326,y:260,color:'#f08093'}]; // Explicação DevIN: Declara variável JavaScript pads para reutilizar valor ou resultado.
            const s={sequence:[],index:0,display:0,wait:0,round:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            function next(){ // Explicação DevIN: Declara função/método next, com seus parâmetros e comportamento.
                s.sequence.push(Math.floor(Math.random()*4)); // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                s.round++;s.index=0;s.display=0;s.wait=750; // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            next(); // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'simon',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                key(key){ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    if(/^Digit[1-4]$/.test(key))this.pick(Number(key.slice(-1))-1); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event){ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    const p=point(event); // Explicação DevIN: Declara variável JavaScript p para reutilizar valor ou resultado.
                    for(let i=0;i<4;i++){const pad=pads[i];if(p.x>=pad.x&&p.x<=pad.x+205&&p.y>=pad.y&&p.y<=pad.y+140){this.pick(i);break;}} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pick(i){ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    if(s.display<s.sequence.length||s.wait>0)return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(i!==s.sequence[s.index]){finish(this);return;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    s.index++; // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    if(s.index===s.sequence.length){this.score+=30;levelUp(this,120);next();} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    if(s.wait>0){s.wait-=delta;return;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(s.display<s.sequence.length){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        s.wait=600;s.display++; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    background(); // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    pads.forEach((pad,i)=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                        const highlighted=s.display>0&&s.display<=s.sequence.length&&s.sequence[s.display-1]===i&&s.wait>150; // Explicação DevIN: Declara variável JavaScript highlighted para reutilizar valor ou resultado.
                        ctx.globalAlpha=highlighted?1:.55; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        bar(pad.x,pad.y,205,140,pad.color);ctx.globalAlpha=1; // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                        ctx.fillStyle='#092343';ctx.font='800 24px system-ui';ctx.fillText(String(i+1),pad.x+18,pad.y+33); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    write(s.display<s.sequence.length?'Memorize a sequência':'Sua vez! Repita os números',20,34,18); // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                    write('Rodada: '+s.round,20,467,16); // Explicação DevIN: Continua expressão ou bloco pertencente a função next.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        function basketEngine(level=1,score=0){ // Explicação DevIN: Declara função/método basketEngine, com seus parâmetros e comportamento.
            const s={x:320,balls:[],spawn:400,miss:0}; // Explicação DevIN: Declara variável JavaScript s para reutilizar valor ou resultado.
            return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                game:'basket',level,score,state:s,ended:false, // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                key(key){ // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    if(['ArrowLeft','KeyA'].includes(key))s.x=clamp(s.x-42,50,590); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if(['ArrowRight','KeyD'].includes(key))s.x=clamp(s.x+42,50,590); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                pointer(event){s.x=clamp(px(event),50,590);}, // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                update(delta){ // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    const d=Math.min(delta,60)/1000; // Explicação DevIN: Declara variável JavaScript d para reutilizar valor ou resultado.
                    s.spawn-=delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if(s.spawn<=0){s.balls.push({x:rand(28,612),y:-16,color:Math.random()<.22?'#f06c83':'#ffd55c'});s.spawn=Math.max(260,850-this.level*50);} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    for(let i=s.balls.length-1;i>=0;i--){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        const b=s.balls[i];b.y+=(165+this.level*19)*d; // Explicação DevIN: Declara variável JavaScript b para reutilizar valor ou resultado.
                        if(b.y>414&&b.y<450&&Math.abs(b.x-s.x)<53){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            if(b.color==='#f06c83'){finish(this);return;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            this.score+=20;levelUp(this,140);s.balls.splice(i,1); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        }else if(b.y>500){ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            if(b.color!=='#f06c83')s.miss++; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            s.balls.splice(i,1); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                            if(s.miss>=5){finish(this);return;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                draw(){ // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    background(); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    s.balls.forEach(b=>circle(b.x,b.y,13,b.color)); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    bar(s.x-57,438,114,15,'#5dd9da');bar(s.x-48,451,96,8,'#276f94'); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                    write('Perdidas: '+s.miss+'/5',20,33);write('Evite as bolas vermelhas',330,33,16); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return { factories: { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            pong:pongEngine,dodge:dodgeEngine,flappy:flappyEngine,target:targetEngine, // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
            collector:collectorEngine,racer:racerEngine,simon:simonEngine,basket:basketEngine // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
        }}; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    window.DevINArcadeExtraGames={metadata,create}; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função basketEngine.
