/* HANAMI seasonal animation on the homepage (late March - early April)

  Include in layout.php :

<!-- ~*~*~*~*~*~*~*~*~*~*~*~* HANAMI ~*~*~*~*~*~*~*~*~*~*~*~* -->
<?php if ($pageId === 'home-index'): ?>
<script type="text/javascript" src="/koohii/hanami.js?v=20260328" defer="1"></script>
<style>
#k-nav_d, #k-nav_m { background: linear-gradient(45deg, #216daa, #882761) !important; }
.petal {
  position: fixed;
  top: 0; left: 0;
  pointer-events: none;
  will-change: transform;
  z-index: 9999;
  clip-path: polygon(50% 15%, 70% 0%, 90% 10%, 100% 35%, 100% 70%, 50% 100%, 0% 70%, 0% 35%, 10% 10%, 30% 0%);
  border-radius: 20% 20% 50% 50% / 10% 10% 80% 80%;
}
</style>
<?php endif; ?>
<!-- ~*~*~*~*~*~*~*~*~*~*~*~* HANAMI ~*~*~*~*~*~*~*~*~*~*~*~* -->
*/

const petalCount = 45;

const petalcolors = [
  "radial-gradient(circle at 50% 50%, #ffa0ab, #ffb7c5)",
  "radial-gradient(circle at 50% 50%, #ffd1dc, #ffc0cb)",
  "radial-gradient(circle at 50% 50%, #ff9fb2, #ffb7c5)",
  "radial-gradient(circle at 50% 50%, #fbb7c0, #ff9fb2)",
];

const petals = [];
let hanaViewportWidth = window.innerWidth;
let hanaViewportHeight = window.innerHeight;

class Petal {
  constructor() {
    this.el = document.createElement("div");
    this.el.className = "petal";
    // Attached directly to the body
    document.body.appendChild(this.el);
    this.reset();
    this.y = Math.random() * hanaViewportHeight;
  }

  reset() {
    this.x = Math.random() * hanaViewportWidth;
    this.y = -40;

    this.width = 10 + Math.random() * 8;
    this.height = this.width * 1.3;

    this.background = petalcolors[Math.floor(Math.random() * petalcolors.length)];
    this.opacity = Math.random() * 0.6 + 0.4;

    this.speedY = 0.7 + Math.random() * 1.1;
    this.speedX = (Math.random() - 0.5) * 0.8;
    this.rotation = Math.random() * 360;
    this.rotationSpeed = (Math.random() - 0.5) * 1.2;

    this.swayAngle = Math.random() * Math.PI * 2;
    this.swaySpeed = 0.01 + Math.random() * 0.02;
    this.swayRadius = 40 + Math.random() * 50;

    this.el.style.width = this.width + "px";
    this.el.style.height = this.height + "px";
    this.el.style.background = this.background;
    this.el.style.opacity = this.opacity;
  }

  update() {
    this.y += this.speedY;
    this.swayAngle += this.swaySpeed;
    this.rotation += this.rotationSpeed;

    const currentX = this.x + Math.sin(this.swayAngle) * this.swayRadius;

    this.el.style.transform = `
                    translate3d(${currentX}px, ${this.y}px, 0) 
                    rotateZ(${this.rotation}deg) 
                    rotateX(${this.rotation * 0.3}deg)
                    rotateY(${Math.sin(this.swayAngle) * 45}deg)
                `;

    if (this.y > hanaViewportHeight + 50) {
      this.reset();
    }
  }
}

window.addEventListener("DOMContentLoaded", () => {
  console.log("*** お花見を楽しんでください! ***");

  for (let i = 0; i < petalCount; i++) {
    petals.push(new Petal());
  }

  function animate() {
    for (let i = 0; i < petals.length; i++) {
      petals[i].update();
    }
    requestAnimationFrame(animate);
  }

  requestAnimationFrame(animate);

  window.addEventListener("resize", () => {
    hanaViewportWidth = window.innerWidth;
    hanaViewportHeight = window.innerHeight;
    petals.forEach((p) => {
      if (p.x > hanaViewportWidth) p.x = Math.random() * hanaViewportWidth;
    });
  });
});
