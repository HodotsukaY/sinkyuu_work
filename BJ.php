<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>ブラックジャック</title>
<style>
    html, body {
        margin: 0;
        padding: 0;
        font-family: sans-serif;
        color: #fff;
        height: 100%;
        background: radial-gradient(circle, #1d6b33 0%, #0f4720 70%, #0a2f14 100%);
        background-size: cover;
        background-attachment: fixed;
    }


    .table-area {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px 0;
    }

    .opponent-area, .player-area { text-align: center; }

    .opponent-cards, .player-cards {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-top: 8px;
    }

    .card-img {
        width: 110px;
        height: 160px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }

    .action-buttons {
        display: flex;
        gap: 30px;
        margin-top: 20px;
        justify-content: center;
    }
    .btn {
        background: #f7c843;
        color: #000;
        padding: 14px 30px;
        font-size: 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        font-weight: bold;
    }

    /* チップバー */
    .chip-bar {
        position: fixed;
        left: 10px;
        bottom: 10px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        z-index: 9999;
    }
    .chip-row {
        display: flex;
        gap: 10px;
    }
    .chip-row.centered {
        margin-left: 40px;
    }
    .chip-bar img {
        width: 70px;
        cursor: pointer;
        transition: transform 0.15s;
    }
    .chip-bar img:hover {
        transform: scale(1.1);
    }

    /* メッセージ表示領域（中央） */
    #message-box {
        position: absolute;
        top: 48%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 40px;
        font-weight: bold;
        color: #fff;
        text-shadow: 0 0 10px rgba(0,0,0,0.7);
        pointer-events: none;
    }
</style>
</head>
<body>

<div id="message-box"></div>

<div class="table-area">

    <!-- 相手の場 -->
    <div class="opponent-area">
        <div id="opponent-name">ディーラー</div>
        <div id="opponent-total">合計: ?</div>
        <div class="opponent-cards" id="opponent-cards"></div>
    </div>

    <!-- プレイヤーの場 -->
    <div class="player-area">
        <div style="height:40px;"></div>

        <div id="player-total">合計: ?</div>
        <div class="player-cards" id="player-cards"></div>

        <div class="action-buttons">
            <button class="btn" id="hit-btn">HIT</button>
            <button class="btn" id="stand-btn">STAND</button>
        </div>
    </div>
</div>

<!-- ▼ チップバー ▼ -->
<div class="chip-bar">
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
</div>
<!-- ▲ チップバー ▲ -->


<script>
const cardBackImg = "https://deckofcardsapi.com/static/img/back.png";

let deckId = "";
let playerCards = [];
let opponentCards = [];
let showOpponentSecondCard = false;

async function newDeck() {
    const res = await fetch("https://deckofcardsapi.com/api/deck/new/shuffle/?deck_count=6");
    const data = await res.json();
    deckId = data.deck_id;
}

async function drawCard(count = 1) {
    const res = await fetch(`https://deckofcardsapi.com/api/deck/${deckId}/draw/?count=${count}`);
    return await res.json();
}

function calcTotal(cards) {
    let total = 0;
    let ace = 0;
    cards.forEach(c => {
        let v = c.value;
        if (["KING","QUEEN","JACK"].includes(v)) v = 10;
        else if (v === "ACE") { v = 11; ace++; }
        else v = Number(v);
        total += v;
    });

    while (total > 21 && ace > 0) {
        total -= 10;
        ace--;
    }
    return total;
}

function renderCards(id, cards) {
    const area = document.getElementById(id);
    area.innerHTML = "";
    cards.forEach((c, i) => {
        const img = document.createElement("img");

        if (id === "opponent-cards" && i === 1 && !showOpponentSecondCard) {
            img.src = cardBackImg;
        } else {
            img.src = c.image;
        }
        img.className = "card-img";
        area.appendChild(img);
    });
}

function updateTotals() {
    document.getElementById("player-total").textContent = `合計: ${calcTotal(playerCards)}`;
    document.getElementById("opponent-total").textContent =
        `合計: ${showOpponentSecondCard ? calcTotal(opponentCards) : "?"}`;
}

function showMessage(t) {
    document.getElementById("message-box").textContent = t;
}

/* -------------------
     ディーラーターン
---------------------- */
async function dealerTurn() {
    showOpponentSecondCard = true;
    renderCards("opponent-cards", opponentCards);
    updateTotals();

    showMessage("ディーラー思考中…");

    await new Promise(r => setTimeout(r, 1200));

    while (calcTotal(opponentCards) < 17) {
        await new Promise(r => setTimeout(r, 1200));
        const d = await drawCard(1);
        opponentCards.push(d.cards[0]);
        renderCards("opponent-cards", opponentCards);
        updateTotals();
    }

    showMessage("");

    setTimeout(() => {
        endGame();
    }, 500);
}

/* -------------------
       勝敗判定
---------------------- */
function endGame() {
    const p = calcTotal(playerCards);
    const o = calcTotal(opponentCards);

    let msg = "";

    if (p > 21) msg = "あなたの負け";
    else if (o > 21) msg = "あなたの勝ち";
    else if (p > o) msg = "あなたの勝ち";
    else if (p < o) msg = "あなたの負け";
    else msg = "引き分け";

    showMessage(msg);
}

/* -------------------
       ゲーム開始
---------------------- */
(async function start() {
    await newDeck();

    const d = await drawCard(4);

    playerCards.push(d.cards[0], d.cards[2]);
    opponentCards.push(d.cards[1], d.cards[3]);

    renderCards("player-cards", playerCards);
    renderCards("opponent-cards", opponentCards);
    updateTotals();
})();

/* -------------------
       HIT
---------------------- */
document.getElementById("hit-btn").addEventListener("click", async () => {
    const d = await drawCard(1);
    playerCards.push(d.cards[0]);
    renderCards("player-cards", playerCards);
    updateTotals();

    if (calcTotal(playerCards) > 21) {
        showOpponentSecondCard = true;
        renderCards("opponent-cards", opponentCards);
        updateTotals();
        endGame();
    }
});

/* -------------------
       STAND
---------------------- */
document.getElementById("stand-btn").addEventListener("click", async () => {
    document.getElementById("hit-btn").disabled = true;
    document.getElementById("stand-btn").disabled = true;

    dealerTurn();
});
</script>

</body>
</html>
