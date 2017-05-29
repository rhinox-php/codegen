<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>\Traits;

use Rhino\Jwt\Jwt;

trait JwtTrait
{
    protected $jwt;

    /**
     * Outputs a JWT token.
     *
     * @param array  $data payload to encrypt into a JWT string
     *
     * @return array
     */
    protected function getJwtToken(array $data): array
    {
        return [
            'data' => [
                'id' => Jwt::encode($data, $this->getJwtEncryptionKey()),
                'type' => 'AuthToken',
            ],
        ];
    }

    protected function getJwtEncryptionKey(): string
    {
        return null;
    }
}
