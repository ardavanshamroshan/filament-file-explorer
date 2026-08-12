/* Docs: mobile menu + Filament CTAs + SPA guide router */
window.__heroAnimations = window.__heroAnimations || [];

(() => {
  const qa = (s, r = document) => [...r.querySelectorAll(s)];
  const menus = qa("[data-mobile-menu]");
  const open = document.querySelector("[data-menu-open]");
  const closers = qa("[data-menu-close]");
  const set = (v) => {
    menus.forEach((m) => (m.hidden = !v));
    document.body.style.overflow = v ? "hidden" : "";
  };
  if (open) open.addEventListener("click", () => set(true));
  closers.forEach((el) =>
    el.addEventListener("click", (e) => {
      if (el.tagName === "A" && el.getAttribute("href") === "#") e.preventDefault();
      set(false);
    }),
  );
})();

function splitChars(el) {
  const text = el.textContent;
  el.setAttribute("aria-label", text.trim());
  el.innerHTML = "";
  const frag = document.createDocumentFragment();
  for (const ch of text) {
    const span = document.createElement("span");
    span.setAttribute("aria-hidden", "true");
    span.style.position = "relative";
    span.style.display = "inline-block";
    span.textContent = ch === " " ? "\u00a0" : ch;
    frag.appendChild(span);
  }
  el.appendChild(frag);
  return [...el.children];
}

function bindHoneyButton(root) {
  const button = root.querySelector("a");
  if (!button || !window.gsap) return;
  const textWrapper = button.querySelector("[data-text]");
  const expandingBg = button.querySelector("[data-expanding-bg]");
  const horizonGlow = button.querySelector("[data-horizon-glow]");
  const rocketContainer = button.querySelector("[data-rocket-container]");
  if (!textWrapper || !expandingBg || !rocketContainer) return;

  button.style.width = button.offsetWidth + "px";
  const chars = splitChars(textWrapper);
  const tl = gsap.timeline({ paused: true });
  tl.to(expandingBg, { scale: 7, duration: 0.4, ease: "power2.out" }, 0);
  if (horizonGlow) {
    tl.to(horizonGlow, { opacity: 1, duration: 0.4, ease: "power2.out" }, 0.04);
  }
  tl.to(textWrapper, { x: 31, duration: 0.35, ease: "power2.inOut" }, 0);
  tl.to(rocketContainer, { x: -106, duration: 0.5, ease: "circ.inOut" }, 0);
  tl.to(chars, { color: "#eaeaea", duration: 0.25, ease: "power2.out", stagger: 0.02 }, 0.05);

  button.addEventListener("mouseenter", () => tl.tweenTo(tl.duration()));
  button.addEventListener("mouseleave", () => tl.tweenTo(0));
  button.addEventListener("focus", () => tl.tweenTo(tl.duration()));
  button.addEventListener("blur", () => tl.tweenTo(0));
}

function bindGhostButton(root) {
  const button = root.querySelector("a");
  if (!button || !window.gsap) return;
  const textWrapper = button.querySelector("[data-text]");
  const arrow = button.querySelector("[data-arrow]");
  const icon = button.querySelector("[data-swap-icon]");
  if (!textWrapper || !arrow || !icon) return;

  button.style.width = button.offsetWidth + "px";
  const chars = splitChars(textWrapper);
  const tl = gsap.timeline({ paused: true });
  tl.to(arrow, { x: 50, opacity: 0, duration: 0.2, ease: "circ.in" }, 0);
  tl.fromTo(icon, { x: -30, opacity: 0 }, { x: -6, opacity: 1, duration: 0.2, ease: "circ.out" }, 0.15);
  tl.to(textWrapper, { x: 26, duration: 0.2, ease: "sine.out" }, 0.1);
  tl.to(chars, { keyframes: { opacity: [1, 0.4, 1] }, duration: 0.15, ease: "sine.inOut", stagger: 0.02 }, 0.1);

  button.addEventListener("mouseenter", () => tl.tweenTo(tl.duration()));
  button.addEventListener("mouseleave", () => tl.tweenTo(0));
  button.addEventListener("focus", () => tl.tweenTo(tl.duration()));
  button.addEventListener("blur", () => tl.tweenTo(0));
}

function startMeteors(root) {
  if (!window.gsap) return;
  root.querySelectorAll("[data-meteor]").forEach((el, i) => {
    const tween = gsap.to(el, {
      duration: 1.5,
      x: -80,
      y: 80,
      ease: "power1.in",
      repeat: -1,
      repeatDelay: 0.5,
      delay: i % 2 === 1 ? 1 : 0,
    });
    window.__heroAnimations.push(tween);
  });
  const rocket = root.querySelector("[data-rocket-bob]");
  if (rocket) {
    const tween = gsap.to(rocket, {
      x: 3,
      y: 3,
      rotate: -3,
      duration: 1.5,
      repeat: -1,
      yoyo: true,
      ease: "sine.inOut",
    });
    window.__heroAnimations.push(tween);
  }
}

function bootCtas() {
  document.querySelectorAll("[data-btn-honey]").forEach((el) => {
    startMeteors(el);
    bindHoneyButton(el);
  });
  document.querySelectorAll("[data-btn-ghost]").forEach(bindGhostButton);
}

/* SPA guide: hash URL, no reload */
function bootGuideSpa() {
  const root = document.querySelector("[data-docs-spa]");
  if (!root) return;

  const panels = [...root.querySelectorAll("[data-doc-panel]")];
  const links = [...document.querySelectorAll("[data-doc-link]")];
  const titleEl = document.querySelector("[data-doc-title]");
  const leadEl = document.querySelector("[data-doc-lead]");
  const titles = JSON.parse(root.getAttribute("data-titles") || "{}");
  const leads = JSON.parse(root.getAttribute("data-leads") || "{}");
  const defaultId = panels[0]?.getAttribute("data-doc-panel") || "installation";

  const show = (id, push) => {
    const slug = titles[id] ? id : defaultId;
    panels.forEach((p) => {
      const on = p.getAttribute("data-doc-panel") === slug;
      p.hidden = !on;
      p.classList.toggle("is-active", on);
    });
    links.forEach((a) => {
      a.classList.toggle("is-active", a.getAttribute("data-doc-link") === slug);
    });
    if (titleEl) titleEl.textContent = titles[slug] || slug;
    if (leadEl) leadEl.textContent = leads[slug] || "";
    document.title = `${titles[slug] || "Docs"} — Filament File Explorer`;
    const hash = `#${slug}`;
    if (push && location.hash !== hash) {
      history.pushState({ slug }, "", hash);
    } else if (!push && location.hash !== hash) {
      history.replaceState({ slug }, "", hash);
    }
    const main = document.querySelector(".docs-main");
    if (main) main.scrollTop = 0;
    window.scrollTo({ top: 0, behavior: "smooth" });
  };

  links.forEach((a) => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      show(a.getAttribute("data-doc-link"), true);
    });
  });

  window.addEventListener("popstate", () => {
    const id = (location.hash || "").replace(/^#/, "") || defaultId;
    show(id, false);
  });

  const initial = (location.hash || "").replace(/^#/, "") || defaultId;
  show(initial, false);
}

function boot() {
  bootCtas();
  bootGuideSpa();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", boot);
} else {
  boot();
}
