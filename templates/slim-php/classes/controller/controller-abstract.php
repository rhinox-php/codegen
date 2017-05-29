<?= '<?php'; ?>

namespace <?= $this->getNamespace(); ?>\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Rhino\InputData\InputData;
use Rhino\Jwt\Jwt;
use Symfony\Component\Validator\Constraints;

abstract class Controller {
    use JwtTrait;

    protected $body;
    protected $input;
    protected $logger;
    protected $request;
    protected $response;

    public function __construct(ContainerInterface $container, ResponseInterface $request, ResponseInterface $response, $params)
    {
        $this->logger = $container->get('logger');
        $this->body = new InputData($request->getParsedBody());
        $this->input = new InputData($request->getParams());
        $this->jwt = $request->jwt;
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Validates an InputData instance against the array of supplied constraints.
     * If violations occurs the error messages property contains details and false is returned.
     *
     * @param \<?= $this->getNamespace(); ?>\Services\InputData      $input
     * @param \Symfony\Component\Validator\Constraints[] $constraints
     * @param bool                                       $allowExtraFields By default if extra/unexpected input fields are supplied a violation will occur. If true extra fields are allowed
     *
     * @return bool True if value, otherwise false
     */
    protected function validateInput(InputData $input, array $constraints, $allowExtraFields = false)
    {
        return $this->validateData($input->getData(), new Constraints\Collection([
            'allowExtraFields' => $allowExtraFields,
            'fields' => $constraints,
        ]));
    }

    /**
     * Validates an array of data against the supplied collection constraint.
     * If violations occurs the error messages property contains details and false is returned.
     *
     * @param mixed[]                                             $data
     * @param \Symfony\Component\Validator\Constraints\Collection $constraints
     *
     * @return bool True if value, otherwise false
     */
    protected function validateData(array $data, Constraints\Collection $constraints)
    {
        $validator = \Symfony\Component\Validator\Validation::createValidator();
        $violations = $validator->validate($data, $constraints);
        foreach ($violations as $violation) {
            $inputName = preg_replace('/[\[\]]/', '', $violation->getPropertyPath(), 2);
            $this->errorMessages->add($inputName, $violation->getMessage());
        }

        return $this->errorMessages->isEmpty();
    }

    /**
     * Validates JSON API structured input data.
     * If violations occurs the error messages property contains details and false is returned.
     *
     * @param mixed[] $parameters
     *
     * @return bool True if value, otherwise false
     */
    protected function validateJsonApi($parameters)
    {
        return $this->validateInput($this->body, [
            'meta' => new Constraints\Optional(),
            'data' => new Constraints\Collection([
                'allowExtraFields' => false,
                'fields' => [
                    'id' => $parameters['id'],
                    'type' => [
                        new Constraints\NotBlank(),
                        new Constraints\EqualTo($parameters['type']),
                    ],
                    'attributes' => new Constraints\Collection([
                        'allowExtraFields' => false,
                        'fields' => $parameters['attributes'],
                    ]),
                    'relationships' => new Constraints\Optional(),
                ],
            ]),
            'included' => new Constraints\Optional(),
        ]);
    }

    /**
     * Outputs a arbitrary error message.
     */
    protected function error($message, $status = 400)
    {
        return $this->getResponse()->withJson([
            'error' => [
                'message' => $message,
            ],
        ], $status);
    }

    /**
     * Outputs a arbitrary client (400 range) error message.
     */
    protected function clientError($message, $status = 400)
    {
        return $this->error($message, $status);
    }

    /**
     * Outputs a arbitrary 404 error message.
     */
    protected function notFound($message)
    {
        return $this->error($message, 404);
    }

    protected function getLogger()
    {
        return $this->logger;
    }

    protected function getBody()
    {
        return $this->body;
    }

    protected function getInput()
    {
        return $this->input;
    }

    protected function getJwt()
    {
        return $this->jwt;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected function getResponse()
    {
        return $this->response;
    }
}
