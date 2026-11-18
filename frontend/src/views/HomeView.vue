<script setup lang="ts">
import {
  Sword,
  Zap,
  Target,
  Shield,
  Trophy,
  UserPlus,
  BookOpen,
  TrendingUp,
  ArrowRight,
  Sparkles,
  Menu,
  X,
} from 'lucide-vue-next'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isMenuOpen = ref(false)

function goToRegister() {
  router.push({ name: 'register' })
}

function scrollToSection(id: string) {
  isMenuOpen.value = false
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
  <div class="landing-page">
    <header class="header">
      <div class="header-inner">
        <RouterLink to="/" class="logo">
          <span class="logo-icon"><Sparkles :size="23" /></span>
          <span>Exolune<span class="logo-dot">.</span></span>
        </RouterLink>

        <nav class="desktop-nav">
          <a href="#how-it-works" @click.prevent="scrollToSection('how-it-works')">
            Як це працює
          </a>
          <a href="#features" @click.prevent="scrollToSection('features')"> Можливості </a>
          <a href="#start" @click.prevent="scrollToSection('start')"> Початок </a>
        </nav>

        <button class="header-cta" @click="goToRegister">
          Почати пригоду <ArrowRight :size="16" />
        </button>

        <button
          class="mobile-menu-button"
          aria-label="Відкрити меню"
          @click="isMenuOpen = !isMenuOpen"
        >
          <X v-if="isMenuOpen" :size="23" />
          <Menu v-else :size="23" />
        </button>
      </div>

      <nav v-if="isMenuOpen" class="mobile-nav">
        <a href="#how-it-works" @click.prevent="scrollToSection('how-it-works')">Як це працює</a>
        <a href="#features" @click.prevent="scrollToSection('features')">Можливості</a>
        <a href="#start" @click.prevent="scrollToSection('start')">Початок</a>
        <button @click="goToRegister">Почати пригоду <ArrowRight :size="16" /></button>
      </nav>
    </header>

    <main>
      <section class="hero">
        <div class="hero-glow"></div>

        <div class="hero-inner">
          <div class="hero-content">
            <div class="eyebrow">
              <Sword :size="15" />
              ТВІЙ ВСЕСВІТ — ТВОЇ ПРИГОДИ
            </div>

            <h1>
              Перетвори своє життя
              <span>на RPG<span class="purple-dot">.</span></span>
            </h1>

            <p class="hero-description">
              Виконуй реальні завдання, здобувай досвід, розвивай характеристики персонажа та крок
              за кроком ставай кращою версією себе.
            </p>

            <div class="hero-actions">
              <button class="primary-button" @click="goToRegister">
                <Sword :size="18" />
                Почати пригоду
                <ArrowRight :size="17" />
              </button>

              <button class="text-button" @click="scrollToSection('how-it-works')">
                Як це працює <ArrowRight :size="16" />
              </button>
            </div>

            <div class="social-proof">
              <div class="avatar-stack">
                <span>A</span>
                <span>M</span>
                <span>D</span>
                <span>K</span>
              </div>
              <div>
                <strong>Твоя пригода починається тут</strong>
                <small>Кожен день — новий шанс стати сильнішим</small>
              </div>
            </div>
          </div>

          <div class="hero-visual">
            <div class="visual-orbit orbit-one"></div>
            <div class="visual-orbit orbit-two"></div>

            <div class="dashboard-preview">
              <div class="preview-topbar">
                <div class="window-dots"><i></i><i></i><i></i></div>
                <span>EXOLUNE / CHARACTER</span>
                <Sparkles :size="15" />
              </div>

              <div class="preview-body">
                <div class="preview-profile">
                  <div class="profile-avatar"><Shield :size="31" /></div>
                  <div class="profile-info">
                    <small>ТВІЙ ПЕРСОНАЖ</small>
                    <strong>Мандрівник</strong>
                    <span>Рівень 01 · Новачок</span>
                  </div>
                  <div class="level-badge">LV. 01</div>
                </div>

                <div class="xp-section">
                  <div class="xp-heading">
                    <span>Досвід</span>
                    <strong>0 / 100 XP</strong>
                  </div>
                  <div class="xp-track"><div></div></div>
                </div>

                <div class="stats-heading">
                  <strong>Характеристики</strong>
                  <span>ТВОЇ СИЛИ</span>
                </div>

                <div class="preview-stats">
                  <div class="stat-item">
                    <div class="stat-icon strength"><Sword :size="18" /></div>
                    <span>Сила</span>
                    <strong>10</strong>
                    <div class="stat-track"><i class="strength-bar"></i></div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-icon intelligence"><BookOpen :size="18" /></div>
                    <span>Інтелект</span>
                    <strong>10</strong>
                    <div class="stat-track"><i class="intelligence-bar"></i></div>
                  </div>
                  <div class="stat-item">
                    <div class="stat-icon discipline"><Target :size="18" /></div>
                    <span>Дисципліна</span>
                    <strong>10</strong>
                    <div class="stat-track"><i class="discipline-bar"></i></div>
                  </div>
                </div>

                <div class="preview-quests-heading">
                  <strong>Щоденні квести</strong>
                  <span>0 / 3</span>
                </div>

                <div class="preview-quest">
                  <div class="quest-icon"><Zap :size="16" /></div>
                  <div><strong>Твій перший крок</strong><small>Виконай корисну дію</small></div>
                  <span class="quest-xp">+30 XP</span>
                </div>
                <div class="preview-quest">
                  <div class="quest-icon book"><BookOpen :size="16" /></div>
                  <div><strong>Нові знання</strong><small>Дізнайся щось нове</small></div>
                  <span class="quest-xp">+25 XP</span>
                </div>
                <div class="preview-quest">
                  <div class="quest-icon target"><Target :size="16" /></div>
                  <div><strong>Тримай фокус</strong><small>Приділи час своїй цілі</small></div>
                  <span class="quest-xp">+20 XP</span>
                </div>
              </div>
            </div>

            <div class="floating-card">
              <div class="floating-icon"><Trophy :size="20" /></div>
              <div>
                <strong>Твій шлях починається!</strong>
                <small>Нові рівні вже чекають на тебе</small>
              </div>
            </div>
          </div>
        </div>

        <div class="hero-bottom">
          <span>01 / СТВОРИ СВОЮ ІСТОРІЮ</span>
          <span>SCROLL TO EXPLORE <ArrowRight :size="14" /></span>
        </div>
      </section>

      <section id="features" class="features-section">
        <div class="section-heading">
          <span class="section-label">СИСТЕМА ПРОГРЕСУ</span>
          <h2>Реальні дії. <span>Відчутний прогрес.</span></h2>
          <p>Усе необхідне, щоб перетворити великі цілі на захопливу щоденну пригоду.</p>
        </div>

        <div class="features-grid">
          <article class="feature-card">
            <div class="feature-icon purple"><Zap :size="23" /></div>
            <span class="feature-number">01 / PROGRESSION</span>
            <h3>Досвід і рівні</h3>
            <p>Здобувай XP за кожну корисну дію та відкривай нові рівні.</p>
          </article>

          <article class="feature-card">
            <div class="feature-icon blue"><Sword :size="23" /></div>
            <span class="feature-number">02 / QUESTS</span>
            <h3>Квести</h3>
            <p>Перетворюй навчання, спорт і звички на зрозумілі завдання.</p>
          </article>

          <article class="feature-card">
            <div class="feature-icon orange"><Shield :size="23" /></div>
            <span class="feature-number">03 / ATTRIBUTES</span>
            <h3>Характеристики</h3>
            <p>Розвивай Силу, Інтелект і Дисципліну реальними вчинками.</p>
          </article>

          <article class="feature-card">
            <div class="feature-icon gold"><Trophy :size="23" /></div>
            <span class="feature-number">04 / ACHIEVEMENTS</span>
            <h3>Досягнення</h3>
            <p>Відкривай нагороди та формуй власну колекцію перемог.</p>
          </article>
        </div>
      </section>

      <section id="how-it-works" class="steps-section">
        <div class="section-heading">
          <span class="section-label">ТРИ ПРОСТИХ КРОКИ</span>
          <h2>Твоя пригода <span>починається сьогодні.</span></h2>
          <p>Не потрібно чекати ідеального моменту. Почни з маленького кроку.</p>
        </div>

        <div class="steps-grid">
          <article class="step-card">
            <div class="step-top">
              <div class="step-icon"><UserPlus :size="25" /></div>
              <span>01</span>
            </div>
            <h3>Створи персонажа</h3>
            <p>Зареєструйся, створи свого персонажа та розпочни власну історію розвитку.</p>
          </article>

          <article class="step-card">
            <div class="step-top">
              <div class="step-icon"><Target :size="25" /></div>
              <span>02</span>
            </div>
            <h3>Обирай квести</h3>
            <p>Обирай завдання з навчання, спорту, здоров'я та особистого розвитку.</p>
          </article>

          <article class="step-card">
            <div class="step-top">
              <div class="step-icon"><TrendingUp :size="25" /></div>
              <span>03</span>
            </div>
            <h3>Розвивайся</h3>
            <p>Виконуй завдання, здобувай XP, підвищуй рівень і відкривай досягнення.</p>
          </article>
        </div>
      </section>

      <section id="start" class="final-section">
        <div class="final-card">
          <div class="final-glow"></div>
          <div class="final-content">
            <span class="section-label"><Sparkles :size="14" /> ЧАС ПОЧИНАТИ</span>
            <h2>Готовий стати головним героєм <span>власного життя?</span></h2>
            <p>Створи персонажа безкоштовно. Твій перший квест уже чекає.</p>
            <button class="primary-button" @click="goToRegister">
              <Sword :size="18" /> Почати пригоду <ArrowRight :size="17" />
            </button>
          </div>
          <div class="final-decoration"><Trophy :size="100" /></div>
        </div>
      </section>
    </main>

    <footer class="footer">
      <div class="footer-inner">
        <RouterLink to="/" class="logo footer-logo">
          <span class="logo-icon"><Sparkles :size="21" /></span>
          <span>Exolune<span class="logo-dot">.</span></span>
        </RouterLink>

        <p>© 2026 Exolune. Твій шлях — твій успіх.</p>

        <div class="footer-links">
          <a href="#features" @click.prevent="scrollToSection('features')">Можливості</a>
          <a href="#how-it-works" @click.prevent="scrollToSection('how-it-works')">Як це працює</a>
          <RouterLink to="/login">Увійти</RouterLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

:global(*) {
  box-sizing: border-box;
}

:global(html) {
  scroll-behavior: smooth;
  scroll-padding-top: 90px;
}

:global(body) {
  margin: 0;
  background: #090d18;
  color: #f5f2ff;
  font-family: 'Manrope', sans-serif;
}

.landing-page {
  min-height: 100vh;
  overflow: hidden;
  background: #090d18;
}

.header {
  position: sticky;
  top: 0;
  z-index: 20;
  background: rgba(9, 13, 24, 0.9);
  border-bottom: 1px solid #252d41;
  backdrop-filter: blur(18px);
}

.header-inner,
.footer-inner {
  width: min(1240px, calc(100% - 64px));
  height: 78px;
  margin: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #f5f2ff;
  font-size: 22px;
  font-weight: 800;
  letter-spacing: -1px;
  text-decoration: none;
}

.logo-icon {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  color: #a78bfa;
  background: #211b36;
  border: 1px solid #49396d;
  border-radius: 11px;
}

.logo-dot,
.purple-dot {
  color: #a78bfa;
}

.desktop-nav {
  display: flex;
  gap: 38px;
}

.desktop-nav a,
.footer-links a {
  color: #929bb2;
  font-size: 13px;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.desktop-nav a:hover,
.footer-links a:hover {
  color: #c4a8ff;
}

.header-cta,
.primary-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  border: 1px solid transparent;
  border-radius: 10px;
  color: white;
  background: linear-gradient(135deg, #8b5cf6, #7041d9);
  font-family: inherit;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 8px 28px rgba(124, 58, 237, 0.2);
  transition:
    transform 0.2s,
    box-shadow 0.2s,
    filter 0.2s;
}

.header-cta {
  padding: 12px 18px;
  font-size: 12px;
}

.primary-button {
  min-height: 52px;
  padding: 0 21px;
  font-size: 13px;
}

.header-cta:hover,
.primary-button:hover {
  transform: translateY(-2px);
  filter: brightness(1.08);
  box-shadow: 0 12px 32px rgba(124, 58, 237, 0.35);
}

.mobile-menu-button,
.mobile-nav {
  display: none;
}

/* HERO */

.hero {
  position: relative;
  min-height: 680px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 80px 0 35px;
  border-bottom: 1px solid #252d41;
}

.hero-glow {
  position: absolute;
  width: 600px;
  height: 600px;
  top: -160px;
  left: -250px;
  border-radius: 50%;
  background: rgba(124, 58, 237, 0.13);
  filter: blur(110px);
  pointer-events: none;
}

.hero-inner {
  position: relative;
  width: min(1240px, calc(100% - 64px));
  margin: auto;
  display: grid;
  grid-template-columns: 0.92fr 1.08fr;
  align-items: center;
  gap: 65px;
}

.hero-content {
  position: relative;
  z-index: 2;
}

.eyebrow,
.section-label {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  color: #bba4ff;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 2px;
}

.eyebrow {
  padding: 10px 13px;
  border: 1px solid #49396d;
  border-radius: 30px;
  background: #211b36;
}

.hero h1 {
  margin: 27px 0 20px;
  color: #f5f2ff;
  font-size: clamp(43px, 5.2vw, 67px);
  line-height: 1.12;
  letter-spacing: -3.5px;
  font-weight: 800;
}

.hero h1 > span {
  display: block;
  color: #a78bfa;
}

.hero-description {
  max-width: 510px;
  margin: 0;
  color: #a1abc1;
  font-size: 15px;
  line-height: 1.9;
}

.hero-actions {
  display: flex;
  align-items: center;
  gap: 25px;
  margin-top: 31px;
}

.text-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 0;
  border: 0;
  color: #d2cce2;
  background: transparent;
  font-family: inherit;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: color 0.2s;
}

.text-button:hover {
  color: #bba4ff;
}

.social-proof {
  display: flex;
  align-items: center;
  gap: 13px;
  margin-top: 38px;
}

.avatar-stack {
  display: flex;
  padding-left: 5px;
}

.avatar-stack span {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  margin-left: -5px;
  border: 2px solid #090d18;
  border-radius: 50%;
  color: white;
  background: linear-gradient(135deg, #8060bb, #3c315d);
  font-size: 10px;
  font-weight: 800;
}

.avatar-stack span:nth-child(2) {
  background: linear-gradient(135deg, #427d91, #294354);
}

.avatar-stack span:nth-child(3) {
  background: linear-gradient(135deg, #a36d69, #503b4c);
}

.avatar-stack span:nth-child(4) {
  background: linear-gradient(135deg, #75885d, #394d45);
}

.social-proof strong,
.social-proof small {
  display: block;
}

.social-proof strong {
  color: #ded9eb;
  font-size: 11px;
}

.social-proof small {
  margin-top: 4px;
  color: #929bb2;
  font-size: 10px;
}

/* HERO PREVIEW */

.hero-visual {
  position: relative;
  min-width: 0;
  padding: 15px 10px 30px;
}

.visual-orbit {
  position: absolute;
  border: 1px solid rgba(167, 139, 250, 0.14);
  border-radius: 50%;
  pointer-events: none;
}

.orbit-one {
  width: 540px;
  height: 540px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.orbit-two {
  width: 450px;
  height: 450px;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.dashboard-preview {
  position: relative;
  z-index: 1;
  max-width: 560px;
  margin: auto;
  overflow: hidden;
  border: 1px solid #34304c;
  border-radius: 15px;
  background: #101625;
  box-shadow:
    0 30px 90px rgba(0, 0, 0, 0.5),
    0 0 60px rgba(124, 58, 237, 0.1);
  transform: perspective(1200px) rotateY(-5deg) rotateX(2deg);
}

.preview-topbar {
  height: 43px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 17px;
  border-bottom: 1px solid #252d41;
  color: #929bb2;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.5px;
}

.preview-topbar > svg {
  color: #a78bfa;
}

.window-dots {
  display: flex;
  gap: 5px;
}

.window-dots i {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #ee7777;
}

.window-dots i:nth-child(2) {
  background: #e7b75e;
}

.window-dots i:nth-child(3) {
  background: #25c69a;
}

.preview-body {
  padding: 20px;
}

.preview-profile {
  display: flex;
  align-items: center;
  gap: 13px;
}

.profile-avatar {
  width: 53px;
  height: 53px;
  display: grid;
  place-items: center;
  border: 1px solid #49396d;
  border-radius: 13px;
  color: #bba4ff;
  background: linear-gradient(135deg, #29213d, #171b2b);
}

.profile-info {
  flex: 1;
}

.profile-info small,
.profile-info strong,
.profile-info span {
  display: block;
}

.profile-info small {
  color: #a78bfa;
  font-size: 8px;
  letter-spacing: 1.4px;
}

.profile-info strong {
  margin-top: 4px;
  color: #f5f2ff;
  font-size: 15px;
}

.profile-info span {
  margin-top: 3px;
  color: #929bb2;
  font-size: 10px;
}

.level-badge {
  padding: 7px 10px;
  border: 1px solid #49396d;
  border-radius: 7px;
  color: #c4a8ff;
  background: #211b36;
  font-size: 9px;
  font-weight: 800;
}

.xp-section {
  margin-top: 22px;
}

.xp-heading,
.stats-heading,
.preview-quests-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.xp-heading {
  color: #a1abc1;
  font-size: 10px;
}

.xp-heading strong {
  color: #c4a8ff;
  font-size: 10px;
}

.xp-track,
.stat-track {
  overflow: hidden;
  border-radius: 20px;
  background: #252d41;
}

.xp-track {
  height: 6px;
  margin-top: 9px;
}

.xp-track div {
  width: 0%;
  height: 100%;
  background: #a78bfa;
}

.stats-heading {
  margin-top: 25px;
}

.stats-heading strong,
.preview-quests-heading strong {
  color: #e7e1f4;
  font-size: 11px;
}

.stats-heading span {
  color: #929bb2;
  font-size: 8px;
  letter-spacing: 1px;
}

.preview-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 9px;
  margin-top: 12px;
}

.stat-item {
  position: relative;
  min-width: 0;
  padding: 11px;
  border: 1px solid #252d41;
  border-radius: 10px;
  background: #151d2d;
}

.stat-icon {
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  margin-bottom: 9px;
  border-radius: 8px;
}

.strength {
  color: #f59b8e;
  background: rgba(239, 115, 102, 0.12);
}

.intelligence {
  color: #8fb8ff;
  background: rgba(96, 165, 250, 0.12);
}

.discipline {
  color: #a78bfa;
  background: rgba(139, 92, 246, 0.12);
}

.stat-item > span {
  color: #a1abc1;
  font-size: 9px;
}

.stat-item > strong {
  float: right;
  color: #eee9fa;
  font-size: 12px;
}

.stat-track {
  height: 4px;
  margin-top: 9px;
}

.stat-track i {
  display: block;
  width: 10%;
  height: 100%;
  border-radius: inherit;
}

.strength-bar {
  background: #ed8d7e;
}

.intelligence-bar {
  background: #7ba9ef;
}

.discipline-bar {
  background: #a78bfa;
}

.preview-quests-heading {
  margin-top: 24px;
}

.preview-quests-heading span {
  color: #a78bfa;
  font-size: 9px;
}

.preview-quest {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 9px;
  padding: 9px;
  border: 1px solid #252d41;
  border-radius: 9px;
  background: #151d2d;
}

.quest-icon {
  width: 31px;
  height: 31px;
  display: grid;
  place-items: center;
  border-radius: 8px;
  color: #f4bc69;
  background: rgba(245, 158, 11, 0.12);
}

.quest-icon.book {
  color: #83b7f6;
  background: rgba(96, 165, 250, 0.12);
}

.quest-icon.target {
  color: #a78bfa;
  background: rgba(139, 92, 246, 0.12);
}

.preview-quest > div:nth-child(2) {
  flex: 1;
  min-width: 0;
}

.preview-quest strong,
.preview-quest small {
  display: block;
}

.preview-quest strong {
  color: #ded9eb;
  font-size: 9px;
}

.preview-quest small {
  margin-top: 3px;
  color: #929bb2;
  font-size: 8px;
}

.quest-xp {
  color: #a78bfa;
  font-size: 9px;
  font-weight: 800;
  white-space: nowrap;
}

.floating-card {
  position: absolute;
  z-index: 2;
  right: -10px;
  bottom: 3px;
  display: flex;
  align-items: center;
  gap: 11px;
  padding: 13px 16px;
  border: 1px solid #49396d;
  border-radius: 12px;
  background: #101625;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.35);
}

.floating-icon {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  border-radius: 10px;
  color: #f4c875;
  background: rgba(245, 158, 11, 0.12);
}

.floating-card strong,
.floating-card small {
  display: block;
}

.floating-card strong {
  color: #f0eafb;
  font-size: 10px;
}

.floating-card small {
  margin-top: 4px;
  color: #929bb2;
  font-size: 9px;
}

.hero-bottom {
  width: min(1240px, calc(100% - 64px));
  margin: 45px auto 0;
  display: flex;
  justify-content: space-between;
  color: #778198;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.7px;
}

.hero-bottom span:last-child {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* FEATURES */

.features-section,
.steps-section {
  width: min(1240px, calc(100% - 64px));
  margin: auto;
  padding: 115px 0;
}

.section-heading {
  max-width: 690px;
  margin-bottom: 48px;
}

.section-label {
  font-size: 9px;
}

.section-heading h2,
.final-card h2 {
  margin: 17px 0 13px;
  color: #f5f2ff;
  font-size: clamp(32px, 4vw, 45px);
  line-height: 1.2;
  letter-spacing: -1.8px;
}

.section-heading h2 span,
.final-card h2 span {
  color: #a78bfa;
}

.section-heading > p,
.final-content > p {
  margin: 0;
  color: #929bb2;
  font-size: 13px;
  line-height: 1.8;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.feature-card {
  min-height: 255px;
  padding: 25px 22px;
  border: 1px solid #252d41;
  border-radius: 14px;
  background: linear-gradient(145deg, #121b2b, #101625);
  transition:
    transform 0.25s,
    border-color 0.25s;
}

.feature-card:hover {
  transform: translateY(-5px);
  border-color: #59447f;
}

.feature-icon {
  width: 45px;
  height: 45px;
  display: grid;
  place-items: center;
  margin-bottom: 24px;
  border-radius: 12px;
}

.feature-icon.purple {
  color: #bba4ff;
  background: #29213d;
}

.feature-icon.blue {
  color: #8db9ff;
  background: rgba(96, 165, 250, 0.13);
}

.feature-icon.orange {
  color: #f3a28b;
  background: rgba(249, 115, 82, 0.12);
}

.feature-icon.gold {
  color: #f2c779;
  background: rgba(245, 158, 11, 0.12);
}

.feature-number {
  color: #929bb2;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.4px;
}

.feature-card h3 {
  margin: 11px 0 9px;
  color: #eeeaf7;
  font-size: 17px;
}

.feature-card p {
  min-height: 55px;
  margin: 0;
  color: #929bb2;
  font-size: 11px;
  line-height: 1.8;
}

.feature-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 19px;
  color: #bba4ff;
  font-size: 10px;
  font-weight: 800;
}

/* STEPS */

.steps-section {
  border-top: 1px solid #252d41;
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 17px;
}

.step-card {
  padding: 27px;
  border: 1px solid #252d41;
  border-radius: 14px;
  background: #101625;
  transition:
    border-color 0.2s,
    transform 0.2s;
}

.step-card:hover {
  transform: translateY(-3px);
  border-color: #49396d;
}

.step-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.step-icon {
  width: 49px;
  height: 49px;
  display: grid;
  place-items: center;
  border: 1px solid #49396d;
  border-radius: 13px;
  color: #bba4ff;
  background: #211b36;
}

.step-top > span {
  color: #778198;
  font-size: 12px;
  font-weight: 800;
}

.step-card h3 {
  margin: 27px 0 10px;
  color: #eeeaf7;
  font-size: 17px;
}

.step-card p {
  margin: 0;
  color: #929bb2;
  font-size: 12px;
  line-height: 1.9;
}

/* FINAL CTA */

.final-section {
  width: min(1240px, calc(100% - 64px));
  margin: 0 auto 100px;
}

.final-card {
  position: relative;
  min-height: 330px;
  display: flex;
  align-items: center;
  overflow: hidden;
  padding: 55px 65px;
  border: 1px solid #49396d;
  border-radius: 19px;
  background: linear-gradient(110deg, #211b36, #101625 70%);
}

.final-glow {
  position: absolute;
  width: 400px;
  height: 400px;
  top: -220px;
  left: 20%;
  border-radius: 50%;
  background: rgba(139, 92, 246, 0.2);
  filter: blur(90px);
}

.final-content {
  position: relative;
  z-index: 1;
  max-width: 650px;
}

.final-content .section-label {
  gap: 8px;
}

.final-card h2 {
  max-width: 620px;
  font-size: clamp(30px, 4vw, 43px);
}

.final-content > p {
  margin-bottom: 25px;
}

.final-decoration {
  position: absolute;
  right: 10%;
  color: rgba(167, 139, 250, 0.09);
  transform: rotate(-12deg);
}

.final-decoration svg {
  width: 190px;
  height: 190px;
}

/* FOOTER */

.footer {
  border-top: 1px solid #252d41;
}

.footer-inner {
  min-height: 85px;
  height: auto;
  gap: 20px;
}

.footer-logo {
  font-size: 19px;
}

.footer-logo .logo-icon {
  width: 32px;
  height: 32px;
}

.footer-inner > p {
  color: #929bb2;
  font-size: 10px;
}

.footer-links {
  display: flex;
  gap: 20px;
}

.footer-links a {
  font-size: 10px;
}

/* TABLET */

@media (max-width: 1000px) {
  .hero-inner {
    grid-template-columns: 1fr 1fr;
    gap: 30px;
  }

  .hero h1 {
    font-size: clamp(39px, 5vw, 54px);
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .floating-card {
    right: -4px;
  }
}

/* MOBILE */

@media (max-width: 720px) {
  .header-inner,
  .footer-inner,
  .hero-inner,
  .hero-bottom,
  .features-section,
  .steps-section,
  .final-section {
    width: calc(100% - 36px);
  }

  .header-inner {
    height: 68px;
  }

  .desktop-nav,
  .header-inner > .header-cta {
    display: none;
  }

  .mobile-menu-button {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border: 1px solid #252d41;
    border-radius: 9px;
    color: #d7ccf5;
    background: #101625;
    cursor: pointer;
  }

  .mobile-nav {
    display: flex;
    flex-direction: column;
    gap: 17px;
    padding: 20px 18px;
    border-top: 1px solid #252d41;
    background: #101625;
  }

  .mobile-nav a {
    color: #c4bfd1;
    font-size: 13px;
    text-decoration: none;
  }

  .mobile-nav a:hover {
    color: #bba4ff;
  }

  .mobile-nav button {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border: 0;
    border-radius: 8px;
    color: white;
    background: #7041d9;
    font-family: inherit;
    font-weight: 700;
    cursor: pointer;
  }

  .hero {
    min-height: auto;
    padding: 65px 0 25px;
  }

  .hero-inner {
    grid-template-columns: 1fr;
    gap: 45px;
  }

  .hero h1 {
    margin-top: 22px;
    font-size: clamp(42px, 11vw, 57px);
    letter-spacing: -2.5px;
  }

  .hero-description {
    font-size: 13px;
  }

  .hero-actions {
    flex-wrap: wrap;
    gap: 15px;
  }

  .primary-button {
    min-height: 49px;
    padding: 0 15px;
    font-size: 11px;
  }

  .social-proof {
    margin-top: 28px;
  }

  .hero-visual {
    padding: 0 0 25px;
  }

  .dashboard-preview {
    transform: none;
  }

  .floating-card {
    right: -2px;
    bottom: 0;
    padding: 10px;
  }

  .floating-card strong {
    font-size: 9px;
  }

  .floating-card small {
    font-size: 8px;
  }

  .orbit-one {
    width: 360px;
    height: 360px;
  }

  .orbit-two {
    width: 300px;
    height: 300px;
  }

  .hero-bottom {
    margin-top: 35px;
    font-size: 7px;
  }

  .features-section,
  .steps-section {
    padding: 75px 0;
  }

  .section-heading {
    margin-bottom: 30px;
  }

  .section-heading h2 {
    font-size: 32px;
  }

  .section-heading > p {
    font-size: 12px;
  }

  .features-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .feature-card {
    min-height: 0;
  }

  .feature-card p {
    min-height: 0;
  }

  .steps-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .step-card {
    padding: 23px;
  }

  .step-card h3 {
    margin-top: 19px;
  }

  .final-section {
    margin-bottom: 65px;
  }

  .final-card {
    min-height: 0;
    padding: 35px 24px;
  }

  .final-card h2 {
    font-size: 31px;
  }

  .final-decoration {
    right: -25px;
    bottom: -20px;
  }

  .final-decoration svg {
    width: 120px;
    height: 120px;
  }

  .final-content {
    max-width: 100%;
  }

  .final-content > p {
    max-width: 290px;
    font-size: 11px;
  }

  .footer-inner {
    flex-wrap: wrap;
    justify-content: center;
    padding: 22px 0;
  }

  .footer-inner > p {
    order: 3;
    width: 100%;
    text-align: center;
  }

  .footer-links {
    gap: 14px;
  }

  .footer-links a {
    font-size: 9px;
  }
}

/* SMALL MOBILE */

@media (max-width: 390px) {
  .preview-body {
    padding: 13px;
  }

  .preview-stats {
    gap: 5px;
  }

  .stat-item {
    padding: 8px;
  }

  .stat-item > span {
    font-size: 8px;
  }

  .floating-card {
    display: none;
  }

  .hero-bottom span:first-child {
    max-width: 130px;
  }
}
</style>
