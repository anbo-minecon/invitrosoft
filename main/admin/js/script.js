/* === Revelar con animación al hacer scroll === */
  const revealables = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e => { if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target);} });
  }, {threshold: .1});
  revealables.forEach(el => io.observe(el));

  /* === SCROLL AUTOMÁTICO LENTO DEL CARRUSEL DE ESTADÍSTICAS === */
  const track = document.getElementById('statsTrack');
  let auto = true;            // estado de auto-scroll
  let dir = 1;                // 1 derecha, -1 izquierda
  let speed = 0.35;           // píxeles por frame (lento y constante)
  let resumeTimer = null;

  function rafScroll(){
    if(!auto) return;                    // pausado por el usuario
    if(track.scrollWidth <= track.clientWidth) return; // no hay desbordamiento
    track.scrollLeft += speed * dir;

    // rebotar en extremos para ida/vuelta
    const atStart = track.scrollLeft <= 0;
    const atEnd = Math.ceil(track.scrollLeft + track.clientWidth) >= track.scrollWidth;
    if(atStart || atEnd){ dir *= -1; }

    requestAnimationFrame(rafScroll);
  }

  // Pausar si el usuario interactúa (wheel, drag, hover)
  ['wheel','touchstart','mousedown','mouseenter'].forEach(ev=>{
    track.addEventListener(ev, ()=>{
      auto = false;
      if(resumeTimer) clearTimeout(resumeTimer);
      // reanudar 4s después de la última interacción
      resumeTimer = setTimeout(()=>{ auto = true; rafScroll(); }, 4000);
    }, {passive:true});
  });

  // Iniciar después de una breve espera al cargar
  window.addEventListener('load', ()=>{
    setTimeout(()=>{ auto = true; rafScroll(); }, 800);
  });


  //perfil de usuario
  document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector(".user-menu-btn");
  const menu = document.querySelector(".dropdown-menu");

  btn.addEventListener("click", () => {
    menu.classList.toggle("show");
  });

  // Cerrar el menú si se hace clic fuera
  document.addEventListener("click", (e) => {
    if (!btn.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove("show");
    }
  });
});

