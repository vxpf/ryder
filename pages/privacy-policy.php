<?php require "includes/header-no-search.php" ?>

<main>
    <div class="container privacy-page">
        <h1>Privacy Policy</h1>
        
        <div class="privacy-section">
            <h2>1. Introduction</h2>
            <p>Welcome to Rydr ("we," "our," or "us"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website and services. Please read this privacy policy carefully. By using our services, you consent to the practices described in this policy.</p>
        </div>
        
        <div class="privacy-section">
            <h2>2. Information We Collect</h2>
            <h3>2.1 Personal Data</h3>
            <p>We may collect personal information that you voluntarily provide to us when you:</p>
            <ul>
                <li>Register for an account</li>
                <li>Make a reservation or booking</li>
                <li>Complete forms on our website</li>
                <li>Contact customer service</li>
                <li>Sign up for our newsletter</li>
            </ul>
            <p>This information may include your name, email address, postal address, phone number, date of birth, driver's license information, payment information, and any other information you choose to provide.</p>
            
            <h3>2.2 Automatically Collected Data</h3>
            <p>When you visit our website, we automatically collect certain information about your device and usage patterns. This may include:</p>
            <ul>
                <li>IP address</li>
                <li>Browser type and version</li>
                <li>Operating system</li>
                <li>Referring website</li>
                <li>Pages viewed and time spent on our website</li>
                <li>Links clicked and actions taken on our website</li>
                <li>Date and time of your visit</li>
            </ul>
        </div>
        
        <div class="privacy-section">
            <h2>3. How We Use Your Information</h2>
            <p>We use your information for various purposes, including:</p>
            <ul>
                <li>Facilitating vehicle rentals and related services</li>
                <li>Processing payments and deposits</li>
                <li>Managing your account and preferences</li>
                <li>Communicating with you about reservations, services, and updates</li>
                <li>Sending promotional emails and newsletters (if opted in)</li>
                <li>Improving our website, products, and services</li>
                <li>Analyzing usage patterns and trends</li>
                <li>Preventing fraudulent activities and ensuring security</li>
                <li>Complying with legal obligations</li>
            </ul>
        </div>
        
        <div class="privacy-section">
            <h2>4. Sharing Your Information</h2>
            <p>We may share your information with:</p>
            <ul>
                <li>Service providers who perform services on our behalf (payment processors, IT service providers, etc.)</li>
                <li>Business partners when necessary to provide you with requested services</li>
                <li>Law enforcement or other governmental authorities when required by law</li>
                <li>Professional advisors such as lawyers, auditors, and insurers</li>
            </ul>
            <p>We do not sell your personal information to third parties.</p>
        </div>
        
        <div class="privacy-section">
            <h2>5. Cookies and Tracking Technologies</h2>
            <p>We use cookies and similar tracking technologies to track activity on our website and hold certain information. Cookies are files with a small amount of data which may include an anonymous unique identifier.</p>
            <p>You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, some portions of our website may not function properly.</p>
            <p>Types of cookies we use:</p>
            <ul>
                <li>Essential cookies: necessary for the website to function properly</li>
                <li>Preference cookies: remember your preferences and settings</li>
                <li>Analytics cookies: help us understand how visitors interact with our website</li>
                <li>Marketing cookies: track your online activity to help advertisers deliver more relevant advertising</li>
            </ul>
        </div>
        
        <div class="privacy-section">
            <h2>6. Data Security</h2>
            <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized or unlawful processing, accidental loss, destruction, or damage. However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>
        </div>
        
        <div class="privacy-section">
            <h2>7. Data Retention</h2>
            <p>We will only retain your personal data for as long as necessary to fulfill the purposes for which we collected it, including for the purposes of satisfying any legal, accounting, or reporting requirements.</p>
        </div>
        
        <div class="privacy-section">
            <h2>8. Your Rights</h2>
            <p>Depending on your location, you may have certain rights regarding your personal data, including:</p>
            <ul>
                <li>The right to access your personal data</li>
                <li>The right to rectification of inaccurate data</li>
                <li>The right to erasure (the "right to be forgotten")</li>
                <li>The right to restrict processing</li>
                <li>The right to data portability</li>
                <li>The right to object to processing</li>
                <li>Rights related to automated decision-making and profiling</li>
            </ul>
            <p>To exercise these rights, please contact us using the information provided in the "Contact Us" section.</p>
        </div>
        
        <div class="privacy-section">
            <h2>9. Children's Privacy</h2>
            <p>Our services are not intended for individuals under the age of 21, and we do not knowingly collect personal information from children under this age. If we learn we have collected personal information from a child under 21, we will delete that information as quickly as possible.</p>
        </div>
        
        <div class="privacy-section">
            <h2>10. Changes to This Privacy Policy</h2>
            <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date. You are advised to review this Privacy Policy periodically for any changes.</p>
        </div>
        
        <div class="privacy-section">
            <h2>11. Contact Us</h2>
            <p>If you have any questions about this Privacy Policy, please contact us at:</p>
            <address>
                Rydr<br>
                Stationsplein 45<br>
                3013 AK Rotterdam<br>
                Netherlands<br>
                Email: privacy@rydr.nl<br>
                Phone: +31 10 123 4567
            </address>
        </div>
        
        <div class="privacy-section last-updated">
            <p>Last updated: <?= date("F d, Y") ?></p>
        </div>
    </div>
</main>

<style>
.privacy-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 2em 0;
}

.privacy-page h1 {
    color: #1A202C;
    margin-bottom: 1.5em;
    text-align: center;
}

.privacy-section {
    margin-bottom: 2em;
    background-color: white;
    padding: 1.5em;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.privacy-section h2 {
    color: #3563E9;
    margin-top: 0;
    margin-bottom: 0.75em;
    font-size: 1.3em;
}

.privacy-section h3 {
    color: #1A202C;
    margin-top: 1em;
    margin-bottom: 0.5em;
    font-size: 1.1em;
}

.privacy-section p {
    color: #596780;
    line-height: 1.6;
    margin-bottom: 1em;
}

.privacy-section ul {
    padding-left: 1.5em;
    color: #596780;
}

.privacy-section ul li {
    margin-bottom: 0.5em;
    line-height: 1.6;
}

.privacy-section address {
    font-style: normal;
    line-height: 1.6;
    color: #596780;
}

.last-updated {
    text-align: right;
    font-style: italic;
    color: #90A3BF;
}
</style>

<?php require "includes/footer.php" ?> 