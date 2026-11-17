/**
 * DevIN Arcade — oito minijogos adicionais.
 * Cada factory respeita o contrato do motor existente: game, level, score,
 * key(), pointer(), update() e draw(). Nenhum jogo executa SQL; o progresso
 * continua sendo salvo pela API autenticada do arcade original.
 */
(() => {
    'use strict';
    const metadata = {
        pong: { title: 'Pong Solo', description: 'Rebata a bola na plataforma e sobreviva às velocidades crescentes.', hint: 'A/D, setas ou toque para mover a plataforma.', genre: 'Habilidade' },
        dodge: { title: 'Chuva de Meteoros', description: 'Desvie dos meteoros e descubra até onde consegue resistir.', hint: 'A/D, setas ou arraste para desviar.', genre: 'Reflexos' },
        flappy: { title: 'Voo Pixel', description: 'Atravesse as aberturas sem tocar nos canos.', hint: 'Espaço, seta para cima ou toque para bater as asas.', genre: 'Arcade' },
        target: { title: 'Alvo Rápido', description: 'Clique nos círculos antes que o tempo acabe para acumular pontos.', hint: 'Toque nos alvos ou clique com o mouse.', genre: 'Reflexos' },
        collector: { title: 'Caça às Estrelas', description: 'Colete estrelas e evite as formas vermelhas.', hint: 'Setas, WASD ou toque na posição desejada.', genre: 'Aventura' },
        racer: { title: 'Desvio Turbo', description: 'Mude de faixa e ultrapasse os carros sem colisões.', hint: 'A/D, setas ou toque à esquerda/direita.', genre: 'Corrida' },
        simon: { title: 'Sequência de Cores', description: 'Memorize e repita uma sequência de quatro quadrantes.', hint: 'Toque nos quadrantes ou use as teclas 1 a 4.', genre: 'Raciocínio' },
        basket: { title: 'Cesta Relâmpago', description: 'Mova a cesta para recolher as bolas que caem.', hint: 'A/D, setas ou arraste para os lados.', genre: 'Habilidade' }
    };

    function create(context) {
        const { ctx, canvas, colors, gameOver, saveProgress } = context;
        const width = 640, height = 480;
        const clamp = (n, lo, hi) => Math.max(lo, Math.min(hi, n));
        const rand = (lo, hi) => lo + Math.random() * (hi - lo);
        const px = (event) => {
            const rect = canvas.getBoundingClientRect();
            return (event.clientX - rect.left) / Math.max(1, rect.width) * width;
        };
        const point = (event) => {
            const rect = canvas.getBoundingClientRect();
            return {
                x: (event.clientX - rect.left) / Math.max(1, rect.width) * width,
                y: (event.clientY - rect.top) / Math.max(1, rect.height) * height
            };
        };
        const background = () => {
            ctx.fillStyle = '#061c39';
            ctx.fillRect(0, 0, width, height);
            ctx.strokeStyle = 'rgba(134,190,255,.10)';
            for (let x = 0; x < width; x += 40) {
                ctx.beginPath(); ctx.moveTo(x, 0); ctx.lineTo(x, height); ctx.stroke();
            }
            for (let y = 0; y < height; y += 40) {
                ctx.beginPath(); ctx.moveTo(0, y); ctx.lineTo(width, y); ctx.stroke();
            }
        };
        const write = (s, x = 20, y = 32, size = 20) => {
            ctx.fillStyle = '#f5faff'; ctx.font = '700 ' + size + 'px system-ui, sans-serif';
            ctx.textAlign = 'left'; ctx.fillText(String(s), x, y);
        };
        const circle = (x, y, r, color) => {
            ctx.beginPath(); ctx.arc(x, y, r, 0, Math.PI * 2);
            ctx.fillStyle = color; ctx.fill();
        };
        const bar = (x,y,w,h,c) => { ctx.fillStyle=c;ctx.fillRect(x,y,w,h); };
        const levelUp = (engine, increment) => {
            const next = 1 + Math.floor(engine.score / increment);
            if (next > engine.level) { engine.level = next; saveProgress(engine); }
        };
        // Apenas uma instância do motor está ativa. gameOver() pertence ao portal
        // e já salva recordes, mostra classificação e impede atualizações extras.
        const finish = (engine) => { if (!engine.ended) gameOver(); };

        function pongEngine(level = 1, score = 0) {
            const s = { x: 320, ballX: 320, ballY: 260, vx: 225, vy: -270, streak: 0 };
            return {
                game:'pong', level, score, state:s, ended:false,
                key(key) {
                    if (['ArrowLeft','KeyA'].includes(key)) s.x = clamp(s.x - 46, 56, 584);
                    if (['ArrowRight','KeyD'].includes(key)) s.x = clamp(s.x + 46, 56, 584);
                },
                pointer(event) { s.x = clamp(px(event),56,584); },
                update(delta) {
                    const d = Math.min(delta, 50) / 1000;
                    s.ballX += s.vx * d; s.ballY += s.vy * d;
                    if (s.ballX < 13 || s.ballX > 627) { s.ballX=clamp(s.ballX,13,627); s.vx *= -1; }
                    if (s.ballY < 12) { s.ballY=12; s.vy=Math.abs(s.vy); }
                    if (s.ballY > 423 && s.ballY < 455 && s.vy > 0 && Math.abs(s.ballX-s.x) < 65) {
                        s.vy = -Math.min(620, Math.abs(s.vy) + 12);
                        s.vx = clamp((s.ballX-s.x)*5, -380,380);
                        s.streak++; this.score += 25; levelUp(this, 125);
                    }
                    if (s.ballY > 494) finish(this);
                },
                draw() {
                    background(); bar(s.x-58,445,116,14,'#53cce7');
                    circle(s.ballX,s.ballY,11,'#ffd15b');
                    write('Rebatidas: '+s.streak,20,34); write('Não deixe a bola cair!',20,462,14);
                }
            };
        }

        function dodgeEngine(level = 1, score = 0) {
            const s={x:320,objects:[],spawn:0,travel:0};
            return {
                game:'dodge',level,score,state:s,ended:false,
                key(key){
                    if(['ArrowLeft','KeyA'].includes(key))s.x=clamp(s.x-40,22,618);
                    if(['ArrowRight','KeyD'].includes(key))s.x=clamp(s.x+40,22,618);
                },
                pointer(event){s.x=clamp(px(event),22,618);},
                update(delta){
                    const d=Math.min(delta,60)/1000;
                    s.travel+=d;this.score=Math.floor(s.travel*15);
                    s.spawn-=delta;
                    if(s.spawn<=0){s.objects.push({x:rand(22,618),y:-20,r:rand(12,24),speed:rand(155,245)+this.level*14});s.spawn=Math.max(210,700-this.level*32);}
                    s.objects.forEach(o=>o.y+=o.speed*d);
                    s.objects=s.objects.filter(o=>o.y<520);
                    if(s.objects.some(o=>Math.hypot(s.x-o.x,430-o.y)<o.r+17))finish(this);
                    levelUp(this,150);
                },
                draw(){
                    background();s.objects.forEach(o=>circle(o.x,o.y,o.r,'#f36d7b'));
                    circle(s.x,430,18,'#58d9e7');circle(s.x,430,7,'#ffffff');
                    write('Sobreviva à chuva!',20,32);
                }
            };
        }

        function flappyEngine(level = 1, score = 0) {
            const s={y:220,vy:0,pipes:[],timer:0,spawn:600};
            const flap=()=>{s.vy=-260;};
            return {
                game:'flappy',level,score,state:s,ended:false,
                key(key){if(['Space','ArrowUp','KeyW'].includes(key))flap();},
                pointer(){flap();},
                update(delta){
                    const d=Math.min(delta,50)/1000;
                    s.vy+=650*d;s.y+=s.vy*d;
                    s.spawn-=delta;
                    if(s.spawn<=0){s.pipes.push({x:665,gap:rand(145,325),scored:false});s.spawn=1550;}
                    for(const p of s.pipes){
                        p.x-=(160+this.level*8)*d;
                        if(!p.scored&&p.x+58<150){p.scored=true;this.score+=30;levelUp(this,120);}
                        if(150+16>p.x&&150-16<p.x+58&&(s.y-16<p.gap-74||s.y+16>p.gap+74))finish(this);
                    }
                    s.pipes=s.pipes.filter(p=>p.x>-65);
                    if(s.y<15||s.y>465)finish(this);
                },
                draw(){
                    background();
                    s.pipes.forEach(p=>{bar(p.x,0,58,p.gap-74,'#43caab');bar(p.x,p.gap+74,58,height-(p.gap+74),'#43caab');});
                    circle(150,s.y,16,'#ffd35c');circle(156,s.y-4,4,'#12395d');
                    write('Toque para voar',20,33);
                }
            };
        }

        function targetEngine(level = 1, score = 0) {
            const s={remaining:45,target:{x:320,y:230,r:37},hit:0};
            const newTarget=()=>{s.target={x:rand(60,580),y:rand(90,415),r:Math.max(16,40-level*2)};};
            return {
                game:'target',level,score,state:s,ended:false,
                key(){},
                pointer(event){
                    const p=point(event);
                    if(Math.hypot(p.x-s.target.x,p.y-s.target.y)<=s.target.r){
                        s.hit++;this.score+=20;levelUp(this,140);newTarget();
                    }
                },
                update(delta){s.remaining=Math.max(0,s.remaining-delta/1000);if(s.remaining<=0)finish(this);},
                draw(){
                    background();circle(s.target.x,s.target.y,s.target.r+9,'#6ab4f5');
                    circle(s.target.x,s.target.y,s.target.r,'#ffd35c');
                    circle(s.target.x,s.target.y,s.target.r*.45,'#e74f61');
                    write('Tempo: '+Math.ceil(s.remaining)+'s',20,33);
                    write('Acertos: '+s.hit,460,33);
                }
            };
        }

        function collectorEngine(level=1,score=0){
            const s={x:320,y:240,stars:[],hazards:[],time:0};
            const make=(bad=false)=>({x:rand(25,615),y:rand(65,450),bad});
            s.stars=[make(),make(),make()];
            s.hazards=[make(true)];
            return {
                game:'collector',level,score,state:s,ended:false,
                key(key){
                    const k={ArrowLeft:[-30,0],KeyA:[-30,0],ArrowRight:[30,0],KeyD:[30,0],ArrowUp:[0,-30],KeyW:[0,-30],ArrowDown:[0,30],KeyS:[0,30]}[key];
                    if(k){s.x=clamp(s.x+k[0],14,626);s.y=clamp(s.y+k[1],52,466);}
                },
                pointer(event){const p=point(event);s.x=clamp(p.x,14,626);s.y=clamp(p.y,52,466);},
                update(delta){
                    s.time+=delta;
                    for(let i=0;i<s.stars.length;i++){
                        if(Math.hypot(s.x-s.stars[i].x,s.y-s.stars[i].y)<26){
                            s.stars[i]=make();this.score+=25;levelUp(this,150);
                            if(this.score%150===0&&s.hazards.length<7)s.hazards.push(make(true));
                        }
                    }
                    for(const h of s.hazards){
                        const d=Math.hypot(s.x-h.x,s.y-h.y)||1;
                        h.x+=(s.x-h.x)/d*delta*.012*(1+this.level*.10);
                        h.y+=(s.y-h.y)/d*delta*.012*(1+this.level*.10);
                        if(d<23)finish(this);
                    }
                },
                draw(){
                    background();
                    s.stars.forEach(p=>{circle(p.x,p.y,12,'#ffd15b');circle(p.x,p.y,4,'#fff');});
                    s.hazards.forEach(p=>circle(p.x,p.y,15,'#fa6977'));
                    circle(s.x,s.y,16,'#55ddc8');write('Colete as estrelas • evite o vermelho',20,33,17);
                }
            };
        }

        function racerEngine(level=1,score=0){
            const lanes=[180,320,460];
            const s={lane:1,cars:[],distance:0,spawn:300,line:0};
            return {
                game:'racer',level,score,state:s,ended:false,
                key(key){
                    if(['ArrowLeft','KeyA'].includes(key))s.lane=clamp(s.lane-1,0,2);
                    if(['ArrowRight','KeyD'].includes(key))s.lane=clamp(s.lane+1,0,2);
                },
                pointer(event){const x=px(event);s.lane=x<250?0:x>390?2:1;},
                update(delta){
                    const d=Math.min(delta,60)/1000;
                    s.distance+=d*(70+this.level*8);this.score=Math.floor(s.distance);
                    s.line=(s.line+delta*.32)%70;s.spawn-=delta;
                    if(s.spawn<=0){
                        s.cars.push({lane:Math.floor(Math.random()*3),y:-90,passed:false});
                        s.spawn=Math.max(420,1150-this.level*42);
                    }
                    for(const c of s.cars){
                        c.y+=(240+this.level*16)*d;
                        if(c.lane===s.lane&&c.y>347&&c.y<440)finish(this);
                    }
                    s.cars=s.cars.filter(c=>c.y<520);
                    levelUp(this,200);
                },
                draw(){
                    background();bar(105,0,430,480,'#163b64');
                    ctx.fillStyle='#e6f2ff';
                    for(let x of [250,390])for(let y=-70+s.line;y<480;y+=70)ctx.fillRect(x-2,y,4,38);
                    s.cars.forEach(c=>{bar(lanes[c.lane]-25,c.y,50,72,'#f16b7c');bar(lanes[c.lane]-17,c.y+12,34,18,'#752d48');});
                    bar(lanes[s.lane]-25,378,50,72,'#57d5e7');bar(lanes[s.lane]-17,390,34,18,'#1c6c86');
                    write('Distância: '+this.score,20,33);
                }
            };
        }

        function simonEngine(level=1,score=0){
            const pads=[{x:106,y:100,color:'#5ecaf5'},{x:326,y:100,color:'#ffd35c'},{x:106,y:260,color:'#69d8b0'},{x:326,y:260,color:'#f08093'}];
            const s={sequence:[],index:0,display:0,wait:0,round:0};
            function next(){
                s.sequence.push(Math.floor(Math.random()*4));
                s.round++;s.index=0;s.display=0;s.wait=750;
            }
            next();
            return {
                game:'simon',level,score,state:s,ended:false,
                key(key){
                    if(/^Digit[1-4]$/.test(key))this.pick(Number(key.slice(-1))-1);
                },
                pointer(event){
                    const p=point(event);
                    for(let i=0;i<4;i++){const pad=pads[i];if(p.x>=pad.x&&p.x<=pad.x+205&&p.y>=pad.y&&p.y<=pad.y+140){this.pick(i);break;}}
                },
                pick(i){
                    if(s.display<s.sequence.length||s.wait>0)return;
                    if(i!==s.sequence[s.index]){finish(this);return;}
                    s.index++;
                    if(s.index===s.sequence.length){this.score+=30;levelUp(this,120);next();}
                },
                update(delta){
                    if(s.wait>0){s.wait-=delta;return;}
                    if(s.display<s.sequence.length){
                        s.wait=600;s.display++;
                    }
                },
                draw(){
                    background();
                    pads.forEach((pad,i)=>{
                        const highlighted=s.display>0&&s.display<=s.sequence.length&&s.sequence[s.display-1]===i&&s.wait>150;
                        ctx.globalAlpha=highlighted?1:.55;
                        bar(pad.x,pad.y,205,140,pad.color);ctx.globalAlpha=1;
                        ctx.fillStyle='#092343';ctx.font='800 24px system-ui';ctx.fillText(String(i+1),pad.x+18,pad.y+33);
                    });
                    write(s.display<s.sequence.length?'Memorize a sequência':'Sua vez! Repita os números',20,34,18);
                    write('Rodada: '+s.round,20,467,16);
                }
            };
        }

        function basketEngine(level=1,score=0){
            const s={x:320,balls:[],spawn:400,miss:0};
            return {
                game:'basket',level,score,state:s,ended:false,
                key(key){
                    if(['ArrowLeft','KeyA'].includes(key))s.x=clamp(s.x-42,50,590);
                    if(['ArrowRight','KeyD'].includes(key))s.x=clamp(s.x+42,50,590);
                },
                pointer(event){s.x=clamp(px(event),50,590);},
                update(delta){
                    const d=Math.min(delta,60)/1000;
                    s.spawn-=delta;
                    if(s.spawn<=0){s.balls.push({x:rand(28,612),y:-16,color:Math.random()<.22?'#f06c83':'#ffd55c'});s.spawn=Math.max(260,850-this.level*50);}
                    for(let i=s.balls.length-1;i>=0;i--){
                        const b=s.balls[i];b.y+=(165+this.level*19)*d;
                        if(b.y>414&&b.y<450&&Math.abs(b.x-s.x)<53){
                            if(b.color==='#f06c83'){finish(this);return;}
                            this.score+=20;levelUp(this,140);s.balls.splice(i,1);
                        }else if(b.y>500){
                            if(b.color!=='#f06c83')s.miss++;
                            s.balls.splice(i,1);
                            if(s.miss>=5){finish(this);return;}
                        }
                    }
                },
                draw(){
                    background();
                    s.balls.forEach(b=>circle(b.x,b.y,13,b.color));
                    bar(s.x-57,438,114,15,'#5dd9da');bar(s.x-48,451,96,8,'#276f94');
                    write('Perdidas: '+s.miss+'/5',20,33);write('Evite as bolas vermelhas',330,33,16);
                }
            };
        }
        return { factories: {
            pong:pongEngine,dodge:dodgeEngine,flappy:flappyEngine,target:targetEngine,
            collector:collectorEngine,racer:racerEngine,simon:simonEngine,basket:basketEngine
        }};
    }
    window.DevINArcadeExtraGames={metadata,create};
})();
