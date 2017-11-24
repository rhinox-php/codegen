<?= '<?php'; ?>

namespace <?= $this->getNamespace('controller-admin-generated'); ?>;

use Rhino\Http\Request;
use Rhino\Http\Response;
use Rhino\InputData\InputData;
use Symfony\Component\Validator\Constraint;

abstract class AbstractController {
    /**
     * @var \Rhino\Http\Request
     */
    protected $request = null;

    /**
     * @var \Rhino\Http\Response
     */
    protected $response = null;

    /**
     * @var \Rhino\InputData\InputData
     */
    protected $input = null;

    /**
     * @var \Rhino\???\ErrorMessages
     */
    protected $errorMessages = null;

    public function render($view, $data) {
        $this->response->callback(function() use($view, $data) {
            $loader = new \Twig_Loader_Filesystem(ROOT . '/src/views');
            $this->twig = new \Twig_Environment($loader, [
                // 'cache' => __DIR__ . '/cache',
            ]);
            $template = $this->twig->load($view . '.twig');
            echo $template->render($data);
        });
        /*
        $response->addResponseType('twig', new class {
            protected $twig;

            public function __construct() {
                $loader = new \Twig_Loader_Filesystem(__DIR__ . '/templates');
                $this->twig = new \Twig_Environment($loader, [
                    // 'cache' => __DIR__ . '/cache',
                ]);
            }

            public function process($view, $data) {
                $template = $this->twig->load($view . '.twig');
                echo $template->render($data);
            }
        });
        */
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function setRequest(Request $request): self {
        $this->request = $request;
        return $this;
    }

    public function getResponse(): Response {
        return $this->response;
    }

    public function setResponse(Response $response): self {
        $this->response = $response;
        return $this;
    }

    public function getInput(): InputData {
        return $this->input;
    }

    public function setInput(InputData $inputData): self {
        $this->input = $inputData;
        return $this;
    }
}
