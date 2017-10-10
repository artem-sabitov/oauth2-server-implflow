<?php

namespace OAuth2\Grant\Implicit;

use ClientStorageInterface;
use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Adapter\AuthorizationAdapter;
use OAuth2\Grant\Implicit\Factory\AuthorizationAdapterFactory;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

class Server implements GrantManagerInterface
{
    /**
     * @var AdapterInterface|null
     */
    protected $adapter = null;

    /**
     * @var ClientStorageInterface|null
     */
    protected $clientStorage = null;

    /**
     * @var ServerRequestInterface|null
     */
    protected $serverRequest = null;

    /**
     * @var Messages
     */
    protected $messages = null;

    /**
     * Server constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(
        ServerRequestInterface $request = null,
        AdapterInterface $adapter = null,
        ClientStorageInterface $storage = null
    ) {
        if ($request !== null) {
            $this->setServerRequest($request);
        }

        if ($adapter !== null) {
            $this->setAdapter($adapter);
        }

        if ($storage !== null) {
            $this->setClientStorage($storage);
        }

        $this->messages = new Messages();
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return GrantResultInterface
     */
    public function authorize(ServerRequestInterface $request = null): GrantResultInterface
    {
        if ($request !== null) {
            $this->setServerRequest($request);
        }

        $adapter = $this->getAdapter();

        $this->validate($adapter);
    }

    /**
     * @param AdapterInterface $adapter
     * @return boolean
     */
    protected function validate(AdapterInterface $adapter)
    {
        $client = $this
            ->getClientStorage()
            ->getClient($adapter->getClientId());

        $redirectUri = $adapter->getRedirectUri();

        $uriIsCorrect = false;
        foreach ($client->getListAvailableRedirectUri() as $uri) {
            if ($redirectUri === $uri) {
                $uriIsCorrect = true;
            }
        }

        if ($uriIsCorrect === false) {
            $this->getMessages()->addErrorMessage(sprintf(
                'The parameter %s `%s` not available to the %s: `%s`',
                AuthorizationAdapter::CLIENT_ID_KEY, $redirectUri
            ));

            return false;
        }
    }

    /**
     * @return null|AdapterInterface
     */
    public function getAdapter()
    {
        if ($this->adapter === null) {
            $this->adapter = AuthorizationAdapterFactory::fromServerRequest($this->getServerRequest());
        }

        return $this->adapter;
    }

    /**
     * @param null|AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return ClientStorageInterface|null
     */
    public function getClientStorage()
    {
        return $this->clientStorage;
    }

    /**
     * @param ClientStorageInterface|null $clientStorage
     */
    public function setClientStorage($clientStorage)
    {
        $this->clientStorage = $clientStorage;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getServerRequest()
    {
        if ($this->serverRequest === null) {
            $this->serverRequest = ServerRequestFactory::fromGlobals();
        }

        return $this->serverRequest;
    }

    /**
     * @param null|ServerRequestInterface $serverRequest
     */
    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * @return Messages
     */
    protected function getMessages()
    {
        return $this->messages;
    }
}
