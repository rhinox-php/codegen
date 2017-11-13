<?php
namespace Rhino\Codegen\Template\JsonApi;

class JsonApi
{
    protected $basePath;
    protected $host;
    protected $title;
    protected $version;
    protected $email;

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $value): self {
        $this->title = $value;
        return $this;
    }

    public function getBasePath(): ?string {
        return $this->basePath;
    }

    public function setBasePath(string $value): self {
        $this->basePath = $value;
        return $this;
    }

    public function getHost(): ?string {
        return $this->host;
    }

    public function setHost(string $value): self {
        $this->host = $value;
        return $this;
    }

    public function getVersion(): ?string {
        return $this->version;
    }

    public function setVersion(string $value): self {
        $this->version = $value;
        return $this;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(string $value): self {
        $this->email = $value;
        return $this;
    }
}
