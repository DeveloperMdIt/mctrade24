{extends file="page.tpl"}

{block name="page_content"}
{literal}
<style>
  /* FAQ‑Styles */
  .faq-wrapper {
    max-width: 800px;
    margin: 2rem auto;
    padding: 0 1rem;
  }
  .faq-wrapper h1 {
    color: #FFA500;
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }
  .faq-item {
    margin-bottom: 1rem;
  }
  .faq-toggle {
    width: 100%;
    background: #333;
    color: #fff;
    border: none;
    padding: 1rem;
    text-align: left;
    position: relative;
    cursor: pointer;
  }
  .faq-toggle::after {
    content: "+";
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #FFA500;
    font-size: 1.5rem;
    transition: transform .2s;
  }
  .faq-answer {
    display: none;
    background: #fff;
    color: #000;
    padding: 1rem;
    border: 1px solid #FFA500;
    border-top: none;
  }
  .faq-item.open .faq-toggle::after { content: "−"; }
  .faq-item.open .faq-answer    { display: block; }
</style>
{/literal}

<div class="faq-wrapper">
  <h1>FAQ</h1>

  <div class="faq-item">
    <button class="faq-toggle">Wie starte ich den 30‑Tage‑Testzeitraum?</button>
    <div class="faq-answer">
      <p>Nach der Registrierung erhältst du eine Bestätigungs‑E‑Mail mit deinem Zugang. Der Testzeitraum beginnt automatisch beim ersten Login.</p>
    </div>
  </div>

  <div class="faq-item">
    <button class="faq-toggle">Wie verlängere ich meine Lizenz?</button>
    <div class="faq-answer">
      <p>Im Backend unter <em>Mein Konto → Lizenzverwaltung</em> wählst du dein Paket aus. Nach Zahlungseingang verlängert sich deine Lizenz sofort.</p>
    </div>
  </div>

  {* Weitere Items hier kopieren *}
</div>

{literal}
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.faq-item').forEach(item => {
      item.querySelector('.faq-toggle')
          .addEventListener('click', () => item.classList.toggle('open'));
    });
  });
</script>
{/literal}
{/block}
