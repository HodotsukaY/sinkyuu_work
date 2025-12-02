<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>ブラックジャック（配りアニメ + NEW GAME）</title>
<style>
/* ---------- 背景 ---------- */
html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  font-family: sans-serif;
  color: #fff;
  background: radial-gradient(circle, #1d6b33 0%, #0f4720 70%, #0a2f14 100%);
  background-size: cover;
  background-attachment: fixed;
}

/* ---------- レイアウト ---------- */
.table-area {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 18px 12px;
  box-sizing: border-box;
  position: relative;
}

.opponent-area, .player-area { text-align: center; width:100%; max-width:900px; }
.opponent-cards, .player-cards {
  display: flex;
  gap: 20px;
  justify-content: center;
  margin-top: 8px;
  min-height: 170px;
}

/* スロット（カードが入る領域） */
.card-slot { width:110px; height:160px; }

/* 実際のカード画像（固定） */
.slot-img {
  width:110px;
  height:160px;
  border-radius:8px;
  box-shadow: 0 0 10px rgba(0,0,0,0.6);
  display:block;
  backface-visibility: hidden;
}

/* 浮遊カード（アニメ用） */
.floating-card {
  position: fixed;
  width:110px;
  height:160px;
  border-radius:8px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.6);
  transform-origin: center;
  will-change: transform, left, top;
  z-index: 9998;
}

/* ---------- ボタン ---------- */
.action-buttons {
  display:flex;
  gap:14px;
  justify-content:center;
  margin-top: 20px;
}
.btn {
  background: #f7c843;
  color: #000;
  padding: 12px 18px;
  font-size: 16px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}
.btn:disabled {
  background: #777 !important;
  color: #ccc !important;
  cursor: default;
  opacity: 0.6;
}

/* ---------- チップバー ---------- */
.chip-bar {
  position: fixed;
  left: 10px;
  bottom: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  z-index: 9999;
}
.chip-row { display:flex; gap:10px; }
.chip-row.centered { margin-left:40px; }
.chip-bar img { width:70px; cursor:pointer; transition: transform .15s; }
.chip-bar img:hover { transform: scale(1.1); }

/* BET ボックス（チップの下） */
#bet-container {
  display:flex;
  align-items:center;
  gap:10px;
  margin-top:6px;
}
#bet-box {
  background: rgba(0,0,0,0.75);
  padding: 8px 14px;
  border-radius: 8px;
  min-width: 160px;
  text-align: center;
  font-size: 18px;
  font-weight: 700;
  color: #39ff14;
}
#bet-controls button {
  background:#333;
  color:#fff;
  border:none;
  padding:6px 10px;
  border-radius:6px;
  cursor:pointer;
  font-weight:700;
}
#bet-controls button:hover{ background:#555 }

/* ---------- メッセージゾーン ---------- */
#center-message {
  height: 110px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  font-weight: 800;
  color: #fff;
  text-shadow: 0 0 10px rgba(0,0,0,0.7);
  opacity: 0;
  transform: scale(0.98);
  transition: opacity .45s ease, transform .35s ease;
  pointer-events: none;
  margin: 12px 0;
}
#center-message.show {
  opacity: 1;
  transform: scale(1);
}

/* 思考中の ... を横に点滅 */
#thinking-dots {
  display: flex;
  gap: 6px;
  margin-left: 8px;
  align-items: center;
}
@keyframes blinkDots {
  0% { opacity: 0; transform: translateY(0); }
  30% { opacity: 1; transform: translateY(0); }
  60% { opacity: 0.4; transform: translateY(-2px); }
  100% { opacity: 0; transform: translateY(0); }
}
#thinking-dots span {
  width:8px;
  height:8px;
  background: #fff;
  border-radius:50%;
  display:inline-block;
  animation: blinkDots 1.2s infinite;
}
#thinking-dots span:nth-child(2){ animation-delay: .15s; }
#thinking-dots span:nth-child(3){ animation-delay: .30s; }

/* レスポンシブ */
@media (max-width:600px) {
  #center-message { font-size: 26px; height: 76px; }
  .chip-bar img { width: 56px; }
  .card-slot, .slot-img, .floating-card { width: 86px; height:126px; }
  #bet-box{ min-width:120px; font-size:16px }
}
</style>
</head>
<body>

<div class="table-area">

  <!-- 相手 -->
  <div class="opponent-area">
    <div id="opponent-name">ディーラー</div>
    <div id="opponent-total">合計: ?</div>
    <div class="opponent-cards" id="opponent-cards"></div>
  </div>

  <!-- 中央メッセージゾーン -->
  <div id="center-message"></div>

  <!-- プレイヤー -->
  <div class="player-area">
    <div style="height:10px;"></div>
    <div id="player-total">合計: ?</div>
    <div class="player-cards" id="player-cards"></div>

    <div class="action-buttons">
      <button class="btn" id="hit-btn">HIT</button>
      <button class="btn" id="stand-btn">STAND</button>
      <button class="btn" id="newgame-btn" style="display:none;">NEW GAME</button>
    </div>
    <div id="blackjack-text" style="text-align:center;font-size:28px;font-weight:bold;margin-top:10px;color:#ffd700;"></div>
  </div>
</div>

<!-- チップバー（そのまま） -->
<div class="chip-bar" id="chip-bar">
  <div class="chip-row">
    <img src="img/chip1.png" data-value="1" />
    <img src="img/chip5.png" data-value="5" />
    <img src="img/chip10.png" data-value="10" />
    <img src="img/chip25.png" data-value="25" />
  </div>
  <div class="chip-row centered">
    <img src="img/chip50.png" data-value="50" />
    <img src="img/chip100.png" data-value="100" />
    <img src="img/chip1000.png" data-value="1000" />
  </div>

  <!-- BET ボックス：チップの下に表示 -->
  <div id="bet-container">
    <div id="bet-box"><span id="bet-amount">0</span> pt</div>
    <div id="bet-controls">
      <button id="bet-max">MAX</button>
      <button id="bet-min">MIN</button>
    </div>
  </div>
</div>

<script>
/* -------------------------
   変数 / 設定
   ------------------------- */
const CARD_BACK = "https://deckofcardsapi.com/static/img/back.png";
const centerBox = document.getElementById("center-message");

let deckId = "";
let playerCards = [];
let opponentCards = [];
let showOpponentSecondCard = false;

const dealingDelay = 480; 
const flipDuration = 380;
const flyDuration = 420;

/* BET 変数 */
let betAmount = 0;
const betAmountEl = () => document.getElementById('bet-amount');
function updateBetBox(){ betAmountEl().textContent = betAmount; }

/* CHIP クリックで加算 */
document.addEventListener('click', (e)=>{
  const t = e.target;
  if(t && t.matches && t.matches('.chip-bar img')){
    const v = Number(t.dataset.value || 0);
    betAmount += v;
    updateBetBox();
  }
});

/* MAX / MIN */
document.addEventListener('DOMContentLoaded', ()=>{
  document.getElementById('bet-max').addEventListener('click', ()=>{ betAmount = 1000; updateBetBox(); });
  document.getElementById('bet-min').addEventListener('click', ()=>{ betAmount = 0; updateBetBox(); });
});

/* UTILITY */
function wait(ms){ return new Promise(r => setTimeout(r, ms)); }

function getSlotRect(container) {
  const placeholder = document.createElement("div");
  placeholder.className = "card-slot";
  placeholder.style.visibility = "hidden";
  container.appendChild(placeholder);
  const rect = placeholder.getBoundingClientRect();
  container.removeChild(placeholder);
  return rect;
}

/* 浮遊カード */
function createFloatingCard(src) {
  const f = document.createElement("img");
  f.src = src;
  f.className = "floating-card";
  f.style.left = "0px";
  f.style.top = "0px";
  f.style.transition = `transform ${flyDuration}ms cubic-bezier(.2,.9,.2,1), left ${flyDuration}ms, top ${flyDuration}ms`;
  document.body.appendChild(f);
  return f;
}

/* カードの飛び出す位置を固定（右上） */
function flyStartRect() {
  return {
    left: window.innerWidth - 140,
    top: 30
  };
}

function flyTo(floating, targetRect, revealFlip=false, finalSrc=null) {
  return new Promise(resolve => {

    const dRect = flyStartRect();

    floating.style.left = `${dRect.left}px`;
    floating.style.top = `${dRect.top}px`;
    floating.style.transform = `translate3d(0px,0px,0) rotateY(0deg)`;

    void floating.offsetWidth;

    const dx = targetRect.left - dRect.left;
    const dy = targetRect.top - dRect.top;

    floating.style.transform = `translate3d(${dx}px, ${dy}px, 0px) rotateY(0deg)`;

    setTimeout(async () => {
      if (revealFlip) {
        floating.style.transition = `transform ${flipDuration/1000}s ease-in`;
        floating.style.transform += " rotateY(90deg)";
        await wait(flipDuration/2);
        if (finalSrc) floating.src = finalSrc;
        floating.style.transform = `translate3d(${dx}px, ${dy}px, 0px) rotateY(0deg)`;
        await wait(flipDuration/2 + 30);
      }
      resolve();
    }, flyDuration + 10);
  });
}

/* スロット */
function appendCardToSlot(container, cardSrc, isBack=false, dataFrontSrc=null) {
  const img = document.createElement("img");
  img.className = "slot-img";
  img.src = isBack ? CARD_BACK : cardSrc;
  if (dataFrontSrc) img.dataset.front = dataFrontSrc;
  container.appendChild(img);
  return img;
}

/* Deck API */
async function newDeck() {
  const res = await fetch("https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=6");
  const data = await res.json();
  deckId = data.deck_id;
}
async function drawCard(count=1) {
  const res = await fetch(`https://deckofcardsapi.com/api/deck/${deckId}/draw/?count=${count}`);
  return await res.json();
}

/* メッセージ */
function showMessage(text) {
  centerBox.classList.remove("show");
  setTimeout(() => { centerBox.innerHTML = text; centerBox.classList.add("show"); }, 30);
}
function showThinking() {
  centerBox.classList.remove("show");
  setTimeout(() => {
    centerBox.innerHTML = `思考中<span id="thinking-dots"><span></span><span></span><span></span></span>`;
    centerBox.classList.add("show");
  }, 30);
}

/* 計算 */
function updateTotals() {
  document.getElementById("player-total").textContent =
    `合計: ${calcTotal(playerCards)}`;
  document.getElementById("opponent-total").textContent =
    `合計: ${showOpponentSecondCard ? calcTotal(opponentCards) : "?"}`;
}

function calcTotal(cards) {
  let total = 0;
  let aces = 0;
  cards.forEach(c => {
    let v = c.value;
    if (["KING","QUEEN","JACK"].includes(v)) v = 10;
    else if (v === "ACE") { v = 11; aces++; }
    else v = Number(v);
    total += v;
  });
  while (total > 21 && aces > 0) { total -= 10; aces--; }
  return total;
}

/* 初期 4 枚 */
async function dealInitialFour() {

  document.getElementById("player-cards").innerHTML = "";
  document.getElementById("opponent-cards").innerHTML = "";
  playerCards = [];
  opponentCards = [];
  showOpponentSecondCard = false;
  document.getElementById("newgame-btn").style.display = "none";

  const seq = [
    { target:"player-cards", reveal:true },
    { target:"opponent-cards", reveal:false },
    { target:"player-cards", reveal:true },
    { target:"opponent-cards", reveal:false }
  ];

  for (let i=0;i<4;i++){
    const card = (await drawCard()).cards[0];
    const container = document.getElementById(seq[i].target);
    const rect = getSlotRect(container);
    const floating = createFloatingCard(CARD_BACK);

    await flyTo(floating, rect, seq[i].reveal, card.image);
    document.body.removeChild(floating);

    if (seq[i].target==="player-cards"){
      appendCardToSlot(container, card.image);
      playerCards.push(card);
    } else {
      const isSecond = (i===3);
      if (isSecond){
        appendCardToSlot(container, null, true, card.image);
      } else {
        appendCardToSlot(container, card.image);
      }
      opponentCards.push(card);
    }
    updateTotals();
  if (playerCards.length === 2 && calcTotal(playerCards) === 21) {
    document.getElementById('blackjack-text').textContent = 'BLACKJACK';
  }
    await wait(dealingDelay);
  }

  document.getElementById("hit-btn").disabled = false;
  document.getElementById("stand-btn").disabled = false;
}

/* player HIT */
async function playerHit() {
  document.getElementById("hit-btn").disabled = true;

  const card = (await drawCard()).cards[0];
  const container = document.getElementById("player-cards");
  const rect = getSlotRect(container);
  const floating = createFloatingCard(CARD_BACK);

  await flyTo(floating, rect, true, card.image);
  document.body.removeChild(floating);

  appendCardToSlot(container, card.image);
  playerCards.push(card);
  updateTotals();

  document.getElementById("hit-btn").disabled = false;
}

/* reveal dealer second */
async function revealDealerSecond() {
  showOpponentSecondCard = true;
  const imgs = document.querySelectorAll("#opponent-cards .slot-img");
  if (imgs.length >= 2) {
    const sec = imgs[1];
    const front = sec.dataset.front;
    if (front) {
      sec.style.transition = `transform ${flipDuration/1000}s ease-in`;
      sec.style.transform = "rotateY(90deg)";
      await wait(flipDuration/2);
      sec.src = front;
      sec.style.transform = "rotateY(0deg)";
      await wait(flipDuration/2 + 20);
      sec.style.transition = "";
      delete sec.dataset.front;
    }
  }
  updateTotals();
}

/* dealerTurn */
async function dealerTurn() {
  document.getElementById("hit-btn").disabled = true;
  document.getElementById("stand-btn").disabled = true;

  showThinking();
  await wait(600 + Math.random()*900);

  await revealDealerSecond();
  await wait(500 + Math.random()*800);

  while (calcTotal(opponentCards) < 17) {
    showThinking();
    await wait(800 + Math.random()*900);

    const card = (await drawCard()).cards[0];
    opponentCards.push(card);

    const container = document.getElementById("opponent-cards");
    const rect = getSlotRect(container);
    const floating = createFloatingCard(CARD_BACK);

    await flyTo(floating, rect, true, card.image);
    document.body.removeChild(floating);

    appendCardToSlot(container, card.image);
    updateTotals();
  }

  showResult();
}

/* showResult */
function showResult() {
  const p = calcTotal(playerCards);
  const o = calcTotal(opponentCards);

  let msg = "";
  if (p > 21) msg = "あなたの負け";
  else if (o > 21) msg = "あなたの勝ち！";
  else if (p > o) msg = "あなたの勝ち！";
  else if (p < o) msg = "あなたの負け";
  else msg = "引き分け";

  centerBox.innerHTML = msg;
  centerBox.classList.add("show");

  document.getElementById("hit-btn").disabled = true;
  document.getElementById("stand-btn").disabled = true;

  document.getElementById("newgame-btn").style.display = "inline-block";
}

/* NEW GAME */
document.getElementById("newgame-btn").addEventListener("click", async () => {
  document.getElementById("newgame-btn").style.display = "none";
  document.getElementById("player-cards").innerHTML = "";
  document.getElementById("opponent-cards").innerHTML = "";
  centerBox.classList.remove("show");
  centerBox.textContent = "";

  playerCards = [];
  opponentCards = [];
  showOpponentSecondCard = false;

  await newDeck();
  await dealInitialFour();
});

/* ボタン */
document.getElementById("hit-btn").addEventListener("click", async () => {
  if (calcTotal(playerCards) <= 21) {
    await playerHit();
    if (calcTotal(playerCards) > 21) {
      await revealDealerSecond();
      showResult();
    }
  }
});
document.getElementById("stand-btn").addEventListener("click", async () => {
  await dealerTurn();
});

/* 初期 */
(async function init(){
  document.getElementById("hit-btn").disabled = true;
  document.getElementById("stand-btn").disabled = true;
  await newDeck();
  await dealInitialFour();
})();
</script>
</body>
</html>
