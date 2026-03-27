<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * NotDisposableEmail
 *
 * Blocks registration using disposable/temporary email services.
 * This prevents spam accounts from abusing free credits.
 */
class NotDisposableEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = strtolower(substr(strrchr($value, '@'), 1));

        if (in_array($domain, $this->getBlockedDomains())) {
            $fail('Disposable or temporary email addresses are not allowed. Please use a real email address.');
        }
    }

    /**
     * Comprehensive list of known disposable email domains.
     */
    protected function getBlockedDomains(): array
    {
        return [
            // Major disposable services
            '10minutemail.com', '10minutemail.net', '10minutemail.org',
            'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'temp-mail.de',
            'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org', 'guerrillamail.de',
            'guerrillamailblock.com', 'grr.la', 'sharklasers.com', 'guerrillamail.info',
            'mailinator.com', 'mailinator.net', 'mailinator2.com',
            'yopmail.com', 'yopmail.fr', 'yopmail.net', 'yopmail.gq',
            'throwaway.email', 'throwaway.me',
            'trashmail.com', 'trashmail.me', 'trashmail.net', 'trashmail.org',
            'maildrop.cc', 'maildrop.gq',
            'dispostable.com',
            'mailnesia.com', 'mailnesia.net',
            'getnada.com', 'getnada.cc',
            'tempinbox.com', 'tempinbox.net',
            'discard.email',
            'fakeinbox.com',
            'emailondeck.com',
            'mohmal.com', 'mohmal.im', 'mohmal.in',
            'burnermail.io',
            'harakirimail.com',
            'mailcatch.com',
            'mintemail.com',
            'tempail.com',
            
            // Widely abused free temp services
            'crazymailing.com',
            'mailhub.pw', 'mailhub.top',
            'dropmail.me',
            'emailfake.com',
            'emailtemporario.com.br',
            'emkei.cz',
            'fakemail.net',
            'filzmail.com',
            'getairmail.com',
            'guerrillamail.biz',
            'haltospam.com',
            'hatespam.org',
            'incognitomail.com', 'incognitomail.org',
            'jetable.org', 'jetable.net', 'jetable.com',
            'junkmail.com',
            'kasmail.com',
            'mailexpire.com',
            'mailforspam.com',
            'mailme.lv',
            'mailmoat.com',
            'mailnull.com',
            'mailshell.com',
            'mailzilla.com',
            'nomail.xl.cx',
            'nospam.ze.tc',
            'nowmymail.com',
            'obobbo.com',
            'owlpic.com',
            'proxymail.eu',
            'rcpt.at',
            'receiveee.com',
            'safetymail.info',
            'sharklasers.com',
            'shieldedmail.com',
            'smellfear.com',
            'sneakemail.com',
            'sogetthis.com',
            'spam4.me',
            'spamavert.com',
            'spambox.us',
            'spamcero.com',
            'spamcorner.net',
            'spamfighter.cf',
            'spamfree24.org',
            'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org',
            'spamhole.com',
            'spaml.com', 'spaml.de',
            'spammotel.com',
            'spamobox.com',
            'spamoff.de',
            'spamstack.net',
            'spamtrail.com',
            'superrito.com',
            'suremail.info',
            'tempemail.co.za', 'tempemail.com', 'tempemail.net',
            'tempmaildemo.com',
            'tempomail.fr',
            'temporaryemail.net', 'temporaryemail.us',
            'temporarymail.org',
            'tempthe.net',
            'thankyou2010.com',
            'thisisnotmyrealemail.com',
            'trashbox.eu',
            'trashdevil.com',
            'trashdevil.de',
            'trashemail.de',
            'trashymail.com', 'trashymail.net',
            'wegwerfmail.de', 'wegwerfmail.net', 'wegwerfmail.org',
            'wuzup.net', 'wuzupmail.net',
            'yapped.net',
            'yet.another.email',
            'yuurok.com',
            'zehnminutenmail.de',
            'zoemail.org',
            
            // Russian/CIS disposable
            'mailforspam.com',
            'tempmail.it',
            'tmpmail.net', 'tmpmail.org',
            
            // Modern disposable services (2024-2026)
            'emailnator.com',
            'mail.tm',
            'internxt.com',
            'tmail.gg',
            'luxusmail.org',
            'mailto.plus',
            'disposablemail.com',
            'fakemailgenerator.com',
            'tempmailo.com',
            'emaildrop.io',
            'temp-mail.id',
            'temp-mail.us',
            'minuteinbox.com',
            'tempemails.io',
            'mailtemp.net',
            'mailsac.com',
            'inboxes.com',
            'mytemp.email',
            'tempmails.net',
            'tempmailer.com', 'tempmailer.de',
            'emailnax.com',
            'cryptogmail.com',
            'protocmail.com',
        ];
    }
}
