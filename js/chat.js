'use strict';

const chatFab = document.getElementById('chatFab');
const chatBox = document.getElementById('chatBox');
const chatClose = document.getElementById('chatClose');
const chatLog = document.getElementById('chatLog');
const chatInput = document.getElementById('chatInput');
const chatSend = document.getElementById('chatSend');


const rules = [
{
keywords: ['アップロード', 'upload', '写真', 'photo', 'アップ'],
answer: '写真をアップするには、左メニューの「写真をアップする」から画像を選んで保存してください。ドラッグ&ドロップもできます！'
},
{
keywords: ['ボード', 'board', '作', 'create', 'つくる','作る'],
answer: 'ボードを作るには、まず画像倉庫からパーツを選んで「編集を始める」を押します。そこで写真を自由に並べられます。'
},
{
keywords: ['背景', '色', 'background', 'color', 'bg'],
answer: '編集ページの上のツールバーに背景の色を選ぶボタンがあります。単色でもグラデーションでも選べますよ。'
},
{
keywords: ['スタンプ', 'sticker', 'stamp'],
answer: '編集ページの「スタンプ」ボタンを押すと、かわいいスタンプを選んでボードに貼れます。ドラッグして動かせます！'
},
{
keywords: ['ログイン', 'login', 'account', 'アカウント', '登録'],
answer: '右上のボタンからログインまたは新規登録ができます。登録するとすぐにボードを作り始められます。'
},
{
keywords: ['削除','delete'],
answer: 'ライブラリでボードを選んで削除できます。削除しても、使っていた画像は倉庫に戻るので安心です。'
},
{
keywords: ['privacy', 'private', 'policy', 'プライベートポリシー','ポリシー', 'プライベート'],
answer: '一番右下の所でプライベートポリシーリンクがあります。'
},
];

// the greeting the bot opens with, just sample example
const welcome = 'こんにちは！M³ の使い方について何でも聞いてください。\n例：「プライベートポリシーはどこにありますか？」';

// fallback when nothing matches
const fallback = 'すみませんですが、うまく理解できませんでした。「アップロード」「ボード」「背景」「ステッカー」などについて聞いてみてください。';

 let opened = false;

function openChat() {
chatBox.hidden = false;

    if (!opened) {
        addMessage(welcome, 'bot');
opened = true;
}

chatInput.focus();
}

function closeChat() {
chatBox.hidden = true;
}

// builds a bubble and scrolls to the bottom
function addMessage(text, who) {
    const bubble = document.createElement('div');
 bubble.className = 'chat-msg ' + who;
    bubble.textContent = text;
    chatLog.appendChild(bubble);
    chatLog.scrollTop = chatLog.scrollHeight;
}

// looks for the first rule whose keyword shows up in the message
function findAnswer(text) {
const lower = text.toLowerCase();
    for (const rule of rules) {
    for (const kw of rule.keywords) {
        if (lower.includes(kw.toLowerCase())) {
return rule.answer;
        }
    }
}
    return fallback;
}

function send() {
    const text = chatInput.value.trim();
if (text === '') return;

addMessage(text, 'user');

chatInput.value = '';

// thinking delay
setTimeout(function () {
    addMessage(findAnswer(text), 'bot');
}, 400);
}

chatFab.addEventListener('click', function () {
chatBox.hidden ? openChat() : closeChat();
});

chatClose.addEventListener('click', closeChat);
chatSend.addEventListener('click', send);

chatInput.addEventListener('keydown', function (e) {
if (e.key === 'Enter') send();
});