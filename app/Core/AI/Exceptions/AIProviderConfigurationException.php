<?php

namespace App\Core\AI\Exceptions;

/**
 * Thrown when an AI provider rejects the call due to missing/invalid
 * credentials, missing API key, or unsupported model. These are
 * configuration errors — never internal server errors.
 */
class AIProviderConfigurationException extends \RuntimeException {}
