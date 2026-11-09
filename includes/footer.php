  </div><!-- /.container -->

  <footer>
    <div class="footer-content">

      <!-- COLUNA 1: ANUNCIE SEU IMÓVEL -->
      <div class="footer-section">
        <h4><i class="fas fa-home"></i> Anuncie Seu Imóvel</h4>
        <p style="font-size: 12px; margin-bottom: 10px; color: #94a3b8;">
          Preencha para cadastrar seu imóvel para venda conosco:
        </p>
        <form class="owner-form" action="https://formsubmit.co/<?= h(EMAIL_CONTATO) ?>" method="POST">
          <input type="hidden" name="_subject" value="Novo Imóvel para Cadastrar - Grupo Pé na Areia">
          <input type="hidden" name="_captcha" value="false">

          <input type="text" id="prop-nome" name="Nome_Proprietario" placeholder="Seu Nome Completo" required>
          <input type="tel" id="prop-whats" name="WhatsApp" placeholder="Seu WhatsApp com DDD" required>
          <select id="prop-tipo" name="Tipo_Imovel" required>
            <option value="">Tipo de Imóvel...</option>
            <?php foreach (tiposImovel() as $valor => $rotulo): ?>
              <option value="<?= h($rotulo) ?>"><?= h($rotulo) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" id="prop-cidade" name="Cidade_Bairro" placeholder="Cidade / Bairro do imóvel" required>
          <textarea id="prop-obs" name="Detalhes" rows="2" placeholder="Valor pretendido / detalhes..."></textarea>

          <button type="submit" class="btn-submit-owner"><i class="fas fa-paper-plane"></i> Enviar p/ E-mail</button>
          <a href="#" onclick="enviarProprietarioWhats(event)" class="btn-whatsapp-owner"><i class="fab fa-whatsapp"></i> Ou envie p/ WhatsApp</a>
        </form>
      </div>

      <!-- COLUNA 2: ATENDIMENTO E LOCALIZAÇÃO -->
      <div class="footer-section">
        <h4>Atendimento e Localização</h4>
        <ul class="footer-contact-list">
          <li>
            <div><strong>Endereço:</strong> <?= h(ENDERECO) ?></div>
          </li>
          <li>
            <div><strong>WhatsApp:</strong> <a href="https://wa.me/<?= h(WHATSAPP_PRINCIPAL) ?>" target="_blank" rel="noopener">(13) 98838-1441</a> / <a href="https://wa.me/<?= h(WHATSAPP_SECUNDARIO) ?>" target="_blank" rel="noopener">(11) 98288-5108</a></div>
          </li>
          <li>
            <div>
              <strong>E-mail:</strong><br>
              <a href="mailto:<?= h(EMAIL_CONTATO) ?>"><?= h(EMAIL_CONTATO) ?></a><br>
              <a href="mailto:<?= h(EMAIL_CONTATO_2) ?>"><?= h(EMAIL_CONTATO_2) ?></a>
            </div>
          </li>
          <li>
            <div><strong>Horário:</strong> Segunda a Sábado das 09h00 às 18h00</div>
          </li>
          <li>
            <div><strong>Domingo – Fechado</strong></div>
          </li>
          <li>
            <div><strong>Grupo Pé na Areia Consultoria Imobiliária</strong></div>
          </li>
          <li>
            <div><strong>CRECI/SP <?= h(CRECI) ?></strong></div>
          </li>
        </ul>

        <div class="social-icons">
          <a href="<?= h(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener" class="social-icon instagram" title="Instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="<?= h(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener" class="social-icon facebook" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?= h(SOCIAL_TIKTOK) ?>" target="_blank" rel="noopener" class="social-icon tiktok" title="TikTok" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
          <a href="<?= h(SOCIAL_LINKEDIN) ?>" target="_blank" rel="noopener" class="social-icon linkedin" title="LinkedIn" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="<?= h(SOCIAL_YOUTUBE) ?>" target="_blank" rel="noopener" class="social-icon youtube" title="YouTube" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Grupo Pé na Areia Imóveis. Todos os direitos reservados.</p>
      <a href="<?= BASE_URL ?>/painel/login.php" class="footer-acesso">Acesso</a>
    </div>
  </footer>

  <script src="<?= assetUrl('/assets/js/site.js') ?>"></script>
</body>
</html>
