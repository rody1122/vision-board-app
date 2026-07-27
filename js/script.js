gsap.registerPlugin(ScrollTrigger);

// loading part .......
window.addEventListener('load', () => {
    setTimeout(() => {
        document.getElementById('loading-screen').classList.add('hidden');
    }, 2000);
});



const cursor = document.getElementById('cursor');
const cursorRing = document.getElementById('cursor-ring');
const neonColors = [

    '#C8394A',
    '#E8C547',
    '#4A7C59',
    '#D4693A',
    '#497bf8'

];

let colorIdx = 0;
let ringX = 0,
    ringY = 0,
    curX = 0,
    curY = 0;

document.addEventListener('mousemove', (e) => {
    curX = e.clientX;
    curY = e.clientY;
    cursor.style.left = curX + 'px';
    cursor.style.top = curY + 'px';
});

(function animRing() {
    ringX += (curX - ringX) * 0.1;
    ringY += (curY - ringY) * 0.1;

    cursorRing.style.left = ringX + 'px';
    cursorRing.style.top = ringY + 'px';
    requestAnimationFrame(animRing);
})();

document.addEventListener(
    'click',
    () => {
        colorIdx = (colorIdx + 1) % neonColors.length;
        const c = neonColors[colorIdx];
        cursor.style.background = c;
        cursorRing.style.borderColor = c;
    });


// shuffle header menu part
// ヘッダーメニューのシャッフル部分
const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz!@#$?';

function shuffleText(el) {
    const original = el.dataset.text;
    let frame = 0;
    const totalFrames = 14;

    const iv = setInterval(() => {
        el.textContent = original.split('').map((ch, i) => {
            if (ch === ' ')
                return ' ';
            if (frame > (i / original.length) * totalFrames)
                return ch;

            return CHARS[Math.floor(Math.random() * CHARS.length)];
        }).join('');
        frame++;

        if (frame > totalFrames) {
            clearInterval(iv);
            el.textContent = original;
        }
    }, 32);

}

document.querySelectorAll('.shuffle-text').forEach(el => {
    const parent = el.closest('a') || el.closest('button');

    if (parent) parent.addEventListener(
        'mouseenter',
        () => shuffleText(el)
    );
});


// entrance zoom part
// 入口ズーム部分
gsap.timeline({
    scrollTrigger: {
        trigger: '.door-intro',
        start: 'top top',
        end: 'bottom bottom',
        scrub: 1.2,
    }
})

    .to('.door-intro-img', {
        scale: 9,
        transformOrigin: '50% 42%',
        ease: 'none',
    }, 0)

    .fromTo('.door-fade-overlay', {
        opacity: 0.45
    }, {
        opacity: 0,
        ease: 'none',
    }, 0)

    .fromTo('.door-title', {
        opacity: 1
    }, {
        opacity: 0,
        duration: 0.1,
        ease: 'none',
    }, 0)

    .fromTo('.door-scroll-hint', {
        opacity: 1
    }, {
        opacity: 0,
        duration: 0.1,
        ease: 'none',
    }, 0);


// entrance of the main area
// メインエリアの入り口
gsap.timeline({
    scrollTrigger: {
        trigger: '.hero',
        start: 'top 80%',
        toggleActions: 'play none none none',
    }
})

    // the title opens up from the middle, like the door parting and letting
    // it through, instead of just fading in over the photo
    // タイトルは、まるで扉が開いて中から出てくるように、中央から開きます。
// 写真の上にただフェードインするのではなく。
    .fromTo('.hero-title', {
        clipPath: 'inset(0% 48% 0% 48%)',
        opacity: 0.25,
        scale: 1.1
    }, {
        clipPath: 'inset(0% 0% 0% 0%)',
        opacity: 1,
        scale: 1,
        duration: 1.1, ease: 'power3.out'
    }, '-=0.3')

    .from('.hero-sub', {
        opacity: 0,
        y: 24,
        duration: 0.6, ease: 'power2.out'
    }, '-=0.4')

    .from('.hero-actions', {
        opacity: 0,
        y: 20,
        duration: 0.5, ease: 'power2.out'
    }, '-=0.3');


// feature grids section, explaining how it works
// グリッド
gsap.from('.feature-card', {
    opacity: 0, y: 40,
    stagger: 0.1, duration: 0.65, ease: 'power2.out',
    scrollTrigger: {
        trigger: '.features-grid',
        start: 'top 80%'
    }
});


// text reveal section
gsap.utils.toArray('.reveal-line').forEach((line, i) => {
    gsap.from(line, {
        opacity: 0, y: 50,
        duration: 0.9, ease: 'power3.out',
        scrollTrigger: {
            trigger: line,
            start: 'top 90%'
        },
        delay: i * 0.08,
    });
});


// circle grow effect section over here
const circleTl = gsap.timeline({
    scrollTrigger: {
        trigger: '.circle-transition-section',
        start: 'top top',
        end: '+=220%',
        scrub: 1,
        pin: true,
    }
});

circleTl
    .fromTo('.circle-logo',
        {
            opacity: 0,
            scale: 0.8
        },
        {
            opacity: 1,
            scale: 1,
            duration: 0.3, ease: 'power2.out'
        }
    )

    .fromTo('.circle-fill',
        {
            clipPath: 'circle(0% at 50% 50%)'
        },

        {
            clipPath: 'circle(150% at 50% 50%)',
            duration: 0.7, ease: 'power2.inOut'
        },
        0.25
    )

    .to('.circle-logo', {
        opacity: 0,
        duration: 0.15
    }, 0.5);



// sliding horizontal card section from here
// スライド
const cardStrip = document.querySelector('.cards-strip');
const stepCards = gsap.utils.toArray('.step-card-full');
const totalCards = stepCards.length;

if (cardStrip && totalCards > 0) {

    gsap.set(cardStrip, {
        width: totalCards * 100 + 'vw'
    });

    gsap.to(cardStrip, {
        x: () => -(cardStrip.scrollWidth - window.innerWidth),
        ease: 'none',
        scrollTrigger: {
            trigger: '.cards-horizontal-section',
            pin: true,
            scrub: 1,
            start: 'top top',
            end: () => '+=' + (cardStrip.scrollWidth - window.innerWidth),
            invalidateOnRefresh: true,
        }
    });

    stepCards.forEach((card, i) => {
        gsap.fromTo(card.querySelector('.step-card-inner'),
            {
                opacity: 0,
                x: 60
            },

            {
                opacity: 1,
                x: 0,
                duration: 0.7, ease: 'power3.out',
                scrollTrigger: {
                    trigger: card,
                    containerAnimation: ScrollTrigger.getById('cards-pin'),
                    start: 'left 80%',
                    toggleActions: 'play none none reverse',
                }
            }
        );
    });
}

// circle patches, consider to remove it. visually might not be so necessary
gsap.utils.toArray('.reveal').forEach((el) => {
    gsap.fromTo(el,
        {
            opacity: 0,
            y: 36
        },

        {
            opacity: 1,
            y: 0,
            duration: 0.75, ease: 'power2.out',

            scrollTrigger: {
                trigger: el,
                start: 'top 88%'
            },
        }
    );
});



// copy badge section from here
const copyBadge = document.querySelector('.copy-badge');
const emailWrap = document.querySelector('.footer-email-wrap');

if (copyBadge && emailWrap) {
    emailWrap.addEventListener('mousemove', (e) => {

        const rect = emailWrap.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        copyBadge.style.left = x + 'px';
        copyBadge.style.top = y + 'px';
        copyBadge.style.transform = 'translate(-50%, -50%)';
    });

    emailWrap.addEventListener('mouseenter', () => {
        copyBadge.style.opacity = '1';
    });

    emailWrap.addEventListener('mouseleave', () => {
        copyBadge.style.opacity = '0';

        if (copyBadge.textContent === 'copied!') {
            copyBadge.textContent = 'copy';
            copyBadge.classList.remove('copied');
        }
    });

    document.querySelector('.footer-email').addEventListener('click', (e) => {
        e.preventDefault();
        navigator.clipboard.writeText('m3creative@gmail.com').then(() => {
            copyBadge.textContent = 'copied!';
            copyBadge.classList.add('copied');

            setTimeout(() => {
                copyBadge.textContent = 'copy';
                copyBadge.classList.remove('copied');
            }, 2000);
        });
    });
}


// login and sign in modal funcion from here
function openModal(tab) {
    tab = tab || 'login';
    document.getElementById('authModal').classList.add('active');
    switchTab(tab);
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('authModal').classList.remove('active');
    document.body.style.overflow = '';
}

function handleOverlayClick(e) {
    if (e.target === document.getElementById('authModal')) closeModal();
}

function switchTab(tab) {
    [
        'login',
        'register'
    ].forEach(t => {
        document.getElementById('tab-' + t).classList.toggle('active', t === tab);
        document.getElementById('panel-' + t).classList.toggle('active', t === tab);
    });
}
document.addEventListener('keydown', (e) => {

    if (e.key === 'Escape') closeModal();

});

// atteeeentiooooooooooooon, dont forget!!!!
//  modal part ends here but needs more adjustment like height and so on.
// not so smooth