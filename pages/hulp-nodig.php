<?php require "includes/header-no-search.php" ?>

<main class="help-page">
    <div class="hero-banner">
        <img src="/assets/images/banner.jpeg" alt="Rydr hulp banner" class="full-width-banner">
        <div class="overlay-text">
            <h1>Hulp Nodig<span class="dot">?</span></h1>
            <p class="subtitle">Wij staan voor u klaar met antwoorden op al uw vragen</p>
        </div>
    </div>

    <div class="container">
        <section class="help-intro">
            <div class="grid">
                <div class="row">
                    <div class="text-content">
                        <h2>Hoe kunnen wij u helpen?</h2>
                        <p>Bij Rydr streven we naar een zorgeloze huurervaring. Heeft u vragen over onze diensten, reserveringen of andere zaken? Op deze pagina vindt u antwoorden op veelgestelde vragen en verschillende manieren om contact met ons op te nemen.</p>
                        <p>Ons klantenserviceteam staat klaar om u te helpen met persoonlijk advies en ondersteuning, zowel voor, tijdens als na uw autohuur.</p>
                    </div>
                </div>
                <div class="row">
                    <div class="contact-options">
                        <div class="contact-option">
                            <div class="option-icon"><i class="fas fa-phone"></i></div>
                            <h3>Telefonisch contact</h3>
                            <p>010 - 123 4567</p>
                            <p class="small">Bereikbaar op werkdagen van 9:00 tot 18:00 en zaterdag van 10:00 tot 16:00</p>
                        </div>
                        <div class="contact-option">
                            <div class="option-icon"><i class="fas fa-envelope"></i></div>
                            <h3>E-mail</h3>
                            <p>klantenservice@rydr.nl</p>
                            <p class="small">Wij reageren binnen 24 uur op uw e-mail</p>
                        </div>
                        <div class="contact-option">
                            <div class="option-icon"><i class="fas fa-comments"></i></div>
                            <h3>Live chat</h3>
                            <p>Chat direct met een medewerker</p>
                            <p class="small">Beschikbaar tijdens kantooruren</p>
                            <button class="button-primary start-chat-btn" onclick="openLiveChat()">Start chat</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="faq-section">
            <h2>Veelgestelde vragen</h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Wat heb ik nodig om een auto te huren?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>Om een auto bij Rydr te huren heeft u het volgende nodig:</p>
                        <ul>
                            <li>Een geldig rijbewijs (minimaal 1 jaar oud)</li>
                            <li>Een geldige creditcard of bankpas op uw naam</li>
                            <li>Een geldig legitimatiebewijs (paspoort of ID-kaart)</li>
                            <li>Minimumleeftijd van 21 jaar (25 jaar voor premium voertuigen)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Hoe kan ik een reservering wijzigen of annuleren?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>U kunt uw reservering eenvoudig wijzigen of annuleren via uw account op onze website of door contact op te nemen met onze klantenservice. Houd rekening met de volgende annuleringsvoorwaarden:</p>
                        <ul>
                            <li>Annulering meer dan 48 uur voor ophalen: volledige restitutie</li>
                            <li>Annulering tussen 24-48 uur voor ophalen: 50% restitutie</li>
                            <li>Annulering minder dan 24 uur voor ophalen: geen restitutie</li>
                        </ul>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Wat is inbegrepen bij de huurprijs?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>In onze standaard huurprijs is het volgende inbegrepen:</p>
                        <ul>
                            <li>Onbeperkt aantal kilometers</li>
                            <li>WA-verzekering</li>
                            <li>Casco verzekering met eigen risico</li>
                            <li>24/7 pechhulp in Nederland</li>
                            <li>BTW</li>
                        </ul>
                        <p>Tegen een meerprijs kunt u kiezen voor extra's zoals een navigatiesysteem, kinderzitje of het verlagen van het eigen risico.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Kan ik de auto op een andere locatie inleveren?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>Ja, bij Rydr is het mogelijk om de auto op een andere locatie in te leveren dan waar u deze heeft opgehaald. Hiervoor rekenen wij een toeslag die afhankelijk is van de afstand tussen de twee locaties. Geef dit aan bij uw reservering of neem contact op met onze klantenservice voor meer informatie.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Wat gebeurt er bij schade aan de huurauto?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>Bij schade aan de huurauto geldt het volgende proces:</p>
                        <ol>
                            <li>Meld de schade direct bij ons via de app of telefonisch</li>
                            <li>Vul het Europees schadeformulier in (aanwezig in de auto)</li>
                            <li>Maak foto's van de schade en de situatie</li>
                            <li>Bij inlevering wordt de schade beoordeeld</li>
                            <li>Het eigen risico (maximaal €750) wordt in rekening gebracht, tenzij u een aanvullende verzekering heeft afgesloten</li>
                        </ol>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Kan ik met de huurauto naar het buitenland?</h3>
                        <div class="toggle-icon"><i class="fas fa-chevron-down"></i></div>
                    </div>
                    <div class="faq-answer">
                        <p>Ja, met onze huurauto's kunt u naar de meeste Europese landen reizen. Voor reizen naar bepaalde landen in Oost-Europa gelden aanvullende voorwaarden. Geef bij uw reservering aan dat u naar het buitenland wilt reizen, zodat wij de juiste verzekeringsdocumenten kunnen klaarmaken.</p>
                    </div>
                </div>
            </div>
        </section>
        
                <section class="contact-form-section">            <h2>Neem contact met ons op</h2>            <p class="section-intro">Heeft u een specifieke vraag die hierboven niet wordt beantwoord? Vul dan onderstaand formulier in en wij nemen zo snel mogelijk contact met u op.</p>                        <div class="contact-form-container">                <form id="contact-form" class="contact-form">                    <div class="form-grid">                        <div class="form-group">                            <label for="name">Volledige naam<span class="required-field">*</span></label>                            <input type="text" id="name" name="name" placeholder="Uw volledige naam" required>                        </div>                        <div class="form-group">                            <label for="email">E-mailadres<span class="required-field">*</span></label>                            <input type="email" id="email" name="email" placeholder="uw@email.nl" required>                        </div>                    </div>                    <div class="form-grid">                        <div class="form-group">                            <label for="subject">Onderwerp<span class="required-field">*</span></label>                            <select id="subject" name="subject" required>                                <option value="">Selecteer een onderwerp</option>                                <option value="reservation">Reservering</option>                                <option value="account">Account</option>                                <option value="payment">Betaling</option>                                <option value="damage">Schade</option>                                <option value="other">Overig</option>                            </select>                        </div>                        <div class="form-group">                            <label for="phone">Telefoonnummer</label>                            <input type="tel" id="phone" name="phone" placeholder="Optioneel">                        </div>                    </div>                    <div class="form-group">                        <label for="message">Uw bericht<span class="required-field">*</span></label>                        <textarea id="message" name="message" rows="5" placeholder="Beschrijf uw vraag of opmerking zo gedetailleerd mogelijk..." required></textarea>                    </div>                    <div class="form-group attachment-field">                        <label for="attachment">Bijlage toevoegen</label>                        <div class="file-upload-wrapper">                            <input type="file" id="attachment" name="attachment">                            <label for="attachment" class="file-upload-label"><i class="fas fa-paperclip"></i> Kies bestand</label>                            <span class="file-name">Geen bestand geselecteerd</span>                        </div>                        <p class="file-help-text">Toegestane bestandstypen: JPG, PNG, PDF (max 5MB)</p>                    </div>                    <div class="form-group privacy-consent">                        <input type="checkbox" id="privacy" name="privacy" required>                        <label for="privacy">Ik ga akkoord met de <a href="/privacy-policy">privacyverklaring</a> en het verwerken van mijn gegevens<span class="required-field">*</span></label>                    </div>                    <div class="form-submit">                        <p class="required-fields-note"><span class="required-field">*</span> Verplichte velden</p>                        <button type="submit" class="button-primary"><i class="fas fa-paper-plane"></i> Versturen</button>                    </div>                </form>            </div>        </section>
        
        <section class="emergency-section">
            <div class="emergency-container">
                <div class="emergency-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="emergency-content">
                    <h3>Noodgeval of pech onderweg?</h3>
                    <p>Bel onze 24/7 noodlijn: <strong>0800 - 123 4567</strong></p>
                    <p>Voor pech of schade aan uw huurauto binnen Nederland of in het buitenland.</p>
                </div>
            </div>
        </section>
    </div>
</main>

<div id="live-chat-container" class="live-chat-container">
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-agent-avatar">
                <img src="assets/images/team/brian-mensah.png" alt="Agent">
            </div>
            <div>
                <h3><i class="fas fa-comments"></i> Live Chat met Klantenservice</h3>
                <div class="chat-status"><span class="status-dot online"></span> Online</div>
            </div>
        </div>
        <div class="chat-header-buttons">
            <button class="end-chat-btn" onclick="endChat()"><i class="fas fa-times-circle"></i> Beëindigen</button>
            <button class="close-chat-btn" onclick="closeLiveChat()"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <div class="chat-messages" id="chat-messages">
        <div class="message system-message">
            <p>Welkom bij de Rydr live chat! Een medewerker is nu verbonden.</p>
        </div>
        <div class="message agent-message">
            <div class="message-avatar">
                <img src="assets/images/team/brian-mensah.png" alt="Agent">
            </div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-name">Brian Mensah</span>
                    <span class="message-time">Nu</span>
                </div>
                <p>Hallo! Mijn naam is Brian, hoe kan ik u vandaag helpen?</p>
            </div>
        </div>
    </div>
    <div class="chat-input">
        <input type="text" id="chat-message-input" placeholder="Typ uw bericht...">
        <button id="send-message-btn"><i class="fas fa-paper-plane"></i></button>
    </div>
    <div id="new-chat-container" class="new-chat-container">
        <button id="new-chat-btn" class="new-chat-btn" onclick="startNewChat()">
            <i class="fas fa-plus-circle"></i> Nieuwe chat starten
        </button>
    </div>
</div>

<div id="chat-button" class="chat-button" onclick="openLiveChat()">
    <div class="chat-button-icon">
        <i class="fas fa-comments"></i>
    </div>
    <span>Chat met ons</span>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<style>
.help-page {
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #333;
    line-height: 1.6;
}

.hero-banner {
    position: relative;
    margin-bottom: 50px;
    overflow: hidden;
    height: 400px;
}

.full-width-banner {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.overlay-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    z-index: 2;
    width: 80%;
}

.hero-banner:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.overlay-text h1 {
    font-size: 48px;
    font-weight: 800;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.overlay-text .subtitle {
    font-size: 20px;
    font-weight: 400;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

.dot {
    color: #ff3b58;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

section {
    margin-bottom: 70px;
}

h2 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 10px;
}

h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background-color: #3563e9;
}

.help-intro .grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: flex-start;
}

.text-content p {
    margin-bottom: 15px;
    font-size: 16px;
}

.contact-options {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.contact-option {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.contact-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.option-icon {
    font-size: 32px;
    color: #3563e9;
    margin-bottom: 15px;
}

.contact-option h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
}

.contact-option p {
    margin-bottom: 5px;
}

.contact-option .small {
    font-size: 13px;
    color: #777;
}

.faq-section {
    background-color: #f8f9fa;
    padding: 60px 0;
    margin-left: -20px;
    margin-right: -20px;
    padding-left: 20px;
    padding-right: 20px;
}

.faq-container {
    max-width: 800px;
    margin: 0 auto;
}

.faq-item {
    background: white;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.faq-question {
    padding: 20px 25px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.faq-question h3 {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.toggle-icon {
    color: #3563e9;
    transition: transform 0.3s ease;
}

.faq-answer {
    padding: 0 25px;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease, padding 0.3s ease;
}

.faq-item.active .faq-answer {
    padding: 0 25px 20px;
    max-height: 500px;
}

.faq-item.active .toggle-icon {
    transform: rotate(180deg);
}

.faq-answer p, .faq-answer ul, .faq-answer ol {
    margin-bottom: 15px;
}

.faq-answer ul, .faq-answer ol {
    padding-left: 20px;
}

.faq-answer li {
    margin-bottom: 8px;
}

.section-intro {
    text-align: center;
    max-width: 700px;
    margin: 0 auto 40px;
    font-size: 18px;
}

.contact-form-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {    margin-bottom: 24px;}.form-group label {    display: block;    margin-bottom: 8px;    font-weight: 600;    font-size: 15px;    color: #333;}.required-field {    color: #ff3b58;    margin-left: 4px;}.form-group input,.form-group select,.form-group textarea {    width: 100%;    padding: 14px 16px;    border: 1px solid #ddd;    border-radius: 8px;    font-family: 'Plus Jakarta Sans', sans-serif;    font-size: 15px;    transition: all 0.2s ease;    background-color: #f9fafb;}.form-group input::placeholder,.form-group textarea::placeholder {    color: #aaa;    font-size: 14px;}.form-group input:hover,.form-group select:hover,.form-group textarea:hover {    border-color: #b0b9c6;}.form-group input:focus,.form-group select:focus,.form-group textarea:focus {    outline: none;    border-color: #3563e9;    box-shadow: 0 0 0 3px rgba(53, 99, 233, 0.15);    background-color: #fff;}.attachment-field {    margin-top: 10px;}.file-upload-wrapper {    display: flex;    align-items: center;    margin-top: 8px;}.file-upload-wrapper input[type="file"] {    width: 0.1px;    height: 0.1px;    opacity: 0;    overflow: hidden;    position: absolute;    z-index: -1;}.file-upload-label {    display: inline-flex;    align-items: center;    padding: 10px 16px;    background-color: #eef1f6;    color: #3563e9;    border-radius: 6px;    font-size: 14px;    font-weight: 600;    cursor: pointer;    transition: all 0.2s;    border: 1px dashed #3563e9;    margin-right: 10px;}.file-upload-label:hover {    background-color: #e6eaf0;}.file-upload-label i {    margin-right: 8px;}.file-name {    font-size: 14px;    color: #777;}.file-help-text {    font-size: 12px;    color: #777;    margin-top: 5px;    margin-bottom: 0;}.privacy-consent {    display: flex;    align-items: flex-start;    background-color: #f9fafb;    padding: 14px;    border-radius: 8px;    border: 1px solid #eee;}.privacy-consent input {    width: auto;    margin-right: 10px;    margin-top: 5px;    transform: scale(1.2);}.privacy-consent label {    font-size: 14px;    font-weight: 400;    line-height: 1.4;}.privacy-consent a {    color: #3563e9;    text-decoration: none;    font-weight: 600;}.privacy-consent a:hover {    text-decoration: underline;}.form-submit {    display: flex;    justify-content: space-between;    align-items: center;    margin-top: 10px;}.required-fields-note {    font-size: 13px;    color: #666;    margin: 0;}.button-primary {    display: inline-flex;    align-items: center;    gap: 8px;    background-color: #3563e9;    color: white;    border: none;    border-radius: 8px;    padding: 14px 24px;    font-weight: 600;    font-size: 16px;    cursor: pointer;    transition: all 0.2s ease;}.button-primary:hover {    background-color: #2954d4;    transform: translateY(-2px);    box-shadow: 0 4px 12px rgba(53, 99, 233, 0.25);}.success-message {    text-align: center;    padding: 30px 20px;}.success-icon {    font-size: 48px;    color: #4CAF50;    margin-bottom: 20px;}.success-message h3 {    font-size: 24px;    margin-bottom: 15px;    color: #333;}.success-message p {    color: #666;    font-size: 16px;    max-width: 500px;    margin: 0 auto;}

.emergency-section {
    margin-top: 50px;
}

.emergency-container {
    background: #fff4f6;
    border: 1px solid #ffccd5;
    border-radius: 10px;
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 25px;
}

.emergency-icon {
    font-size: 40px;
    color: #ff3b58;
}

.emergency-content h3 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
    color: #ff3b58;
}

.emergency-content p {
    margin-bottom: 5px;
}

@media (max-width: 992px) {
    .help-intro .grid,
    .contact-options {
        grid-template-columns: 1fr;
    }
    
    .overlay-text h1 {
        font-size: 36px;
    }
    
    .overlay-text .subtitle {
        font-size: 18px;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .hero-banner {
        height: 300px;
    }
    
    .overlay-text h1 {
        font-size: 28px;
    }
    
    .overlay-text .subtitle {
        font-size: 16px;
    }
    
    section {
        margin-bottom: 50px;
    }
    
    h2 {
        font-size: 26px;
    }
    
    .contact-options {
        grid-template-columns: 1fr;
    }
    
    .emergency-container {
        flex-direction: column;
        text-align: center;
    }
}

.start-chat-btn {
    margin-top: 15px;
    padding: 8px 15px;
    font-size: 14px;
}

.chat-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #3563e9;
    color: white;
    border-radius: 50px;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(53, 99, 233, 0.3);
    z-index: 999;
    transition: all 0.3s ease;
}

.chat-button:hover {
    background-color: #2954d4;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(53, 99, 233, 0.4);
}

.chat-button-icon {
    font-size: 20px;
}

.live-chat-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 380px;
    height: 520px;
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
    z-index: 1000;
    display: none;
    overflow: hidden;
    border: 1px solid #e0e0e0;
}

.chat-header {
    padding: 15px;
    background-color: #3563e9;
    color: white;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-agent-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid white;
}

.chat-agent-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-header h3 {
    margin: 0;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-status {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 3px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-dot.online {
    background-color: #4CAF50;
}

.chat-header-buttons {
    display: flex;
    gap: 10px;
}

.close-chat-btn, .end-chat-btn {
    background: none;
    border: none;
    color: white;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
}

.end-chat-btn {
    background-color: rgba(255, 255, 255, 0.2);
    padding: 5px 10px;
    border-radius: 15px;
    transition: background-color 0.2s;
}

.end-chat-btn:hover {
    background-color: rgba(255, 255, 255, 0.3);
}

.close-chat-btn:hover {
    color: rgba(255, 255, 255, 0.8);
}

.chat-messages {
    flex-grow: 1;
    padding: 20px;
    overflow-y: auto;
    background-color: #f5f7fb;
    display: flex;
    flex-direction: column;
    gap: 15px;
    scroll-behavior: smooth;
}

.message {
    max-width: 80%;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.system-message {
    align-self: center;
    background-color: #f0f0f0;
    padding: 8px 12px;
    border-radius: 15px;
    font-size: 13px;
    color: #666;
    text-align: center;
    width: auto;
    max-width: 90%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.user-message {
    align-self: flex-end;
    background-color: #3563e9;
    color: white;
    padding: 12px 16px;
    border-radius: 18px 18px 0 18px;
    box-shadow: 0 2px 4px rgba(53, 99, 233, 0.2);
}

.agent-message {
    align-self: flex-start;
    display: flex;
    gap: 10px;
}

.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}

.message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.message-content {
    background-color: white;
    padding: 12px 16px;
    border-radius: 18px 18px 18px 0;
    border: 1px solid #e0e0e0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.message-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 12px;
}

.message-name {
    font-weight: 600;
    color: #333;
}

.message-time {
    color: #999;
}

.message p {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}

.chat-input {
    padding: 15px;
    display: flex;
    gap: 10px;
    border-top: 1px solid #e0e0e0;
    background-color: white;
}

.chat-input input {
    flex-grow: 1;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.chat-input input:focus {
    outline: none;
    border-color: #3563e9;
    box-shadow: 0 0 0 3px rgba(53, 99, 233, 0.1);
}

.chat-input input:disabled {
    background-color: #f5f5f5;
    cursor: not-allowed;
}

.chat-input button {
    background-color: #3563e9;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s, transform 0.2s;
}

.chat-input button:hover {
    background-color: #2954d4;
    transform: scale(1.05);
}

.chat-input button:disabled {
    background-color: #b0bec5;
    cursor: not-allowed;
    transform: none;
}

.new-chat-container {
    padding: 15px;
    text-align: center;
    border-top: 1px solid #e0e0e0;
    background-color: white;
    display: none;
}

.new-chat-btn {
    background-color: #3563e9;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.new-chat-btn:hover {
    background-color: #2954d4;
    transform: translateY(-1px);
}

.typing-indicator {
    align-self: flex-start;
}

.typing-indicator .message-content {
    padding: 8px 16px;
}

.typing-indicator .message-content p {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 13px;
}

.typing-indicator .message-content p::after {
    content: "...";
    animation: typing 1.5s infinite;
}

@keyframes typing {    0% { content: "."; }    33% { content: ".."; }    66% { content: "..."; }}/* Form validation styles */.form-group input.error,.form-group select.error,.form-group textarea.error {    border-color: #ff3b58;    background-color: #fff0f3;}.form-group input.error:focus,.form-group select.error:focus,.form-group textarea.error:focus {    box-shadow: 0 0 0 3px rgba(255, 59, 88, 0.15);}/* Shake animation for form validation */@keyframes shake {    0%, 100% {transform: translateX(0);}    10%, 30%, 50%, 70%, 90% {transform: translateX(-5px);}    20%, 40%, 60%, 80% {transform: translateX(5px);}}.shake {    animation: shake 0.5s ease-in-out;}/* Form transition effects */.contact-form .form-group {    transition: all 0.3s ease;}.contact-form-container {    position: relative;    transition: height 0.3s ease;}/* Loading spinner animation */@keyframes spin {    0% { transform: rotate(0deg); }    100% { transform: rotate(360deg); }}.fa-spinner {    animation: spin 1s linear infinite;}</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ toggle functionality
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Close all other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle current item
            item.classList.toggle('active');
        });
    });
    
        // Contact form submission    const contactForm = document.getElementById('contact-form');        if (contactForm) {        // File upload handling        const fileInput = document.getElementById('attachment');        const fileNameDisplay = document.querySelector('.file-name');                if (fileInput) {            fileInput.addEventListener('change', function() {                if (this.files && this.files.length > 0) {                    const fileName = this.files[0].name;                    const fileSize = (this.files[0].size / 1024 / 1024).toFixed(2); // Convert to MB                                        if (fileSize > 5) {                        alert('Het bestand is te groot. Maximum grootte is 5MB.');                        this.value = '';                        fileNameDisplay.textContent = 'Geen bestand geselecteerd';                    } else {                        fileNameDisplay.textContent = fileName + ' (' + fileSize + 'MB)';                    }                } else {                    fileNameDisplay.textContent = 'Geen bestand geselecteerd';                }            });        }                // Form validation and submission        contactForm.addEventListener('submit', function(e) {            e.preventDefault();                        // Basic form validation            let isValid = true;            const requiredFields = contactForm.querySelectorAll('[required]');                        requiredFields.forEach(field => {                if (!field.value.trim()) {                    isValid = false;                    field.classList.add('error');                                        // Add shake animation if field is empty                    field.classList.add('shake');                    setTimeout(() => {                        field.classList.remove('shake');                    }, 500);                } else {                    field.classList.remove('error');                }            });                        // Email validation            const emailField = document.getElementById('email');            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;                        if (emailField.value && !emailPattern.test(emailField.value)) {                isValid = false;                emailField.classList.add('error');            }                        if (!isValid) {                return false;            }                        // Here you would normally send the form data to your server            // For demo purposes, we'll just show a success message                        const formData = new FormData(contactForm);            let formValues = {};                        for (let [key, value] of formData.entries()) {                formValues[key] = value;            }                        console.log('Form submitted with values:', formValues);                        // Show loading state            const submitBtn = contactForm.querySelector('button[type="submit"]');            const originalBtnText = submitBtn.innerHTML;            submitBtn.disabled = true;            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Versturen...';                        // Simulate server request            setTimeout(() => {                // Show success message                contactForm.innerHTML = `                    <div class="success-message">                        <div class="success-icon"><i class="fas fa-check-circle"></i></div>                        <h3>Bedankt voor uw bericht!</h3>                        <p>We hebben uw vraag ontvangen en nemen zo snel mogelijk contact met u op. U ontvangt binnen 24 uur een bevestiging via e-mail.</p>                    </div>                `;            }, 1500);        });                // Clear errors when user starts typing        const formInputs = contactForm.querySelectorAll('input, textarea, select');        formInputs.forEach(input => {            input.addEventListener('input', function() {                this.classList.remove('error');            });        });    }
    
    // Live chat functionality
    const chatInput = document.getElementById('chat-message-input');
    const sendButton = document.getElementById('send-message-btn');
    const chatMessages = document.getElementById('chat-messages');
    
    if (sendButton && chatInput) {
        sendButton.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (message) {
            // Add user message
            const userMessage = document.createElement('div');
            userMessage.className = 'message user-message';
            userMessage.innerHTML = `<p>${escapeHtml(message)}</p>`;
            chatMessages.appendChild(userMessage);
            
            // Clear input
            chatInput.value = '';
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Simulate agent typing
            setTimeout(() => {
                const typingIndicator = document.createElement('div');
                typingIndicator.className = 'message agent-message typing-indicator';
                typingIndicator.innerHTML = `
                    <div class="message-avatar">
                        <img src="assets/images/team/brian-mensah.png" alt="Agent">
                    </div>
                    <div class="message-content">
                        <p>Brian is aan het typen</p>
                    </div>
                `;
                chatMessages.appendChild(typingIndicator);
                chatMessages.scrollTop = chatMessages.scrollHeight;
                
                // Simulate agent response after delay
                setTimeout(() => {
                    // Remove typing indicator
                    chatMessages.removeChild(typingIndicator);
                    
                    // Add agent response
                    const agentMessage = document.createElement('div');
                    agentMessage.className = 'message agent-message';
                    
                    // Generate a response based on the user's message
                    let response = getAutomaticResponse(message);
                    
                    agentMessage.innerHTML = `
                        <div class="message-avatar">
                            <img src="assets/images/team/brian-mensah.png" alt="Agent">
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-name">Brian Mensah</span>
                                <span class="message-time">Nu</span>
                            </div>
                            <p>${response}</p>
                        </div>
                    `;
                    chatMessages.appendChild(agentMessage);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }, 1500);
            }, 500);
        }
    }
    
    function getAutomaticResponse(message) {
        message = message.toLowerCase();
        
        if (message.includes('reservering') || message.includes('boeken') || message.includes('huren')) {
            return 'U kunt eenvoudig een auto reserveren via onze website. Ga naar "Ons aanbod", kies een auto en klik op "Huur nu". Kan ik u verder helpen met het reserveringsproces?';
        } else if (message.includes('prijs') || message.includes('kosten') || message.includes('betalen')) {
            return 'Onze prijzen variëren per voertuig en huurperiode. Alle prijzen zijn inclusief btw, verzekering en onbeperkt aantal kilometers. Heeft u een specifieke auto in gedachten?';
        } else if (message.includes('annuleren') || message.includes('wijzigen')) {
            return 'U kunt uw reservering tot 48 uur voor de ophaaldatum kosteloos annuleren of wijzigen. Dit kunt u doen via uw account of door contact op te nemen met onze klantenservice.';
        } else if (message.includes('schade') || message.includes('ongeval') || message.includes('pech')) {
            return 'Bij schade of pech kunt u onze 24/7 noodlijn bellen op 0800-123 4567. Wij zorgen dan voor passende hulp en een vervangende auto indien nodig.';
        } else if (message.includes('hallo') || message.includes('goedemorgen') || message.includes('goedemiddag') || message.includes('hoi')) {
            return 'Hallo! Hoe kan ik u vandaag helpen met uw autohuur?';
        } else if (message.includes('bedankt') || message.includes('dank')) {
            return 'Graag gedaan! Heeft u nog andere vragen?';
        } else {
            return 'Dank voor uw bericht. Kunt u wat meer details geven zodat ik u beter kan helpen? Of heeft u een specifieke vraag over onze autoverhuur?';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

function openLiveChat() {
    document.getElementById('chat-button').style.display = 'none';
    const chatContainer = document.getElementById('live-chat-container');
    chatContainer.style.display = 'flex';
    document.getElementById('chat-message-input').focus();
    
    // Hide new chat button when opening chat
    document.getElementById('new-chat-container').style.display = 'none';
}

function closeLiveChat() {
    document.getElementById('live-chat-container').style.display = 'none';
    document.getElementById('chat-button').style.display = 'flex';
}

function endChat() {
    const chatMessages = document.getElementById('chat-messages');
    
    // Add system message about ending the chat
    const systemMessage = document.createElement('div');
    systemMessage.className = 'message system-message';
    systemMessage.innerHTML = '<p>U heeft de chat beëindigd. Bedankt voor het gebruik van onze live chat service.</p>';
    chatMessages.appendChild(systemMessage);
    
    // Disable input field and send button
    document.getElementById('chat-message-input').disabled = true;
    document.getElementById('send-message-btn').disabled = true;
    
    // Show the new chat button
    document.getElementById('new-chat-container').style.display = 'block';
    
    // Scroll to bottom to show system message
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function startNewChat() {
    const chatMessages = document.getElementById('chat-messages');
    
    // Clear chat history
    while (chatMessages.firstChild) {
        chatMessages.removeChild(chatMessages.firstChild);
    }
    
    // Add welcome messages
    const welcomeMessage = document.createElement('div');
    welcomeMessage.className = 'message system-message';
    welcomeMessage.innerHTML = '<p>Welkom bij de Rydr live chat! Een medewerker is nu verbonden.</p>';
    chatMessages.appendChild(welcomeMessage);
    
    const agentMessage = document.createElement('div');
    agentMessage.className = 'message agent-message';
    agentMessage.innerHTML = `
        <div class="message-avatar">
            <img src="assets/images/team/brian-mensah.png" alt="Agent">
        </div>
        <div class="message-content">
            <div class="message-header">
                <span class="message-name">Brian Mensah</span>
                <span class="message-time">Nu</span>
            </div>
            <p>Hallo! Mijn naam is Brian, hoe kan ik u vandaag helpen?</p>
        </div>
    `;
    chatMessages.appendChild(agentMessage);
    
    // Re-enable input field and send button
    document.getElementById('chat-message-input').disabled = false;
    document.getElementById('send-message-btn').disabled = false;
    
    // Hide the new chat button
    document.getElementById('new-chat-container').style.display = 'none';
    
    // Focus on input field
    document.getElementById('chat-message-input').focus();
}
</script>

<?php require "includes/footer.php" ?> 