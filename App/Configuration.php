<?php

namespace App;

use App\Auth\DbAuthenticator;
use Framework\Core\ErrorHandler;
use Framework\DB\DefaultConventions;

class Configuration
{
    public const APP_NAME = 'Apartmány pod Roháčmi';

    public const FW_VERSION = '3.0.6';

    public const DB_HOST = 'db';
    public const DB_NAME = 'apartmany_rohace';
    public const DB_USER = 'apartmany_user';
    public const DB_PASS = 'admin';

    public const LOGIN_URL = '?c=auth&a=login';

    public const ROOT_LAYOUT = 'root';

    public const SHOW_SQL_QUERY = false;

    public const DB_CONVENTIONS_CLASS = DefaultConventions::class;

    public const SHOW_EXCEPTION_DETAILS = true;

    public const AUTH_CLASS = DbAuthenticator::class;

    public const ERROR_HANDLER_CLASS = ErrorHandler::class;

    public const UPLOAD_DIR = 'uploads' . DIRECTORY_SEPARATOR;

    public const UPLOAD_URL = '/uploads/';

    public const IDENTITY_SESSION_KEY = 'fw.session.user.identity';
}
