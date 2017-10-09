<?php
/**
 * Created by PhpStorm.
 * User: marius
 * Date: 28.08.17
 * Time: 21:33
 */

namespace OCA\Passwords\Db;

use JsonSerializable;

/**
 * Class Revision
 *
 * @method string getTitle()
 * @method void setTitle(string $title)
 * @method string getUrl()
 * @method void setUrl(string $url)
 * @method string getLogin()
 * @method void setLogin(string $login)
 * @method string getPassword()
 * @method void setPassword(string $password)
 * @method string getNotes()
 * @method void setNotes(string $notes)
 * @method string getHash()
 * @method string getPasswordId()
 * @method void setPasswordId(string $passwordId)
 * @method void setHash(string $hash)
 * @method int getStatus()
 * @method void setStatus(int $status)
 *
 * @package OCA\Passwords\Db
 */
class Revision extends AbstractEncryptedEntity {

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $notes;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $passwordId;

    /**
     * Password constructor.
     */
    public function __construct() {
        $this->addType('url', 'string');
        $this->addType('hash', 'string');
        $this->addType('login', 'string');
        $this->addType('notes', 'string');
        $this->addType('title', 'string');
        $this->addType('password', 'string');
        $this->addType('passwordId', 'string');

        $this->addType('status', 'integer');

        parent::__construct();
    }
}