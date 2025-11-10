import os

from dotenv import load_dotenv
from flask import Flask
from flask_wtf import CSRFProtect

from .extensions import db, login_manager, migrate
from .models import User


csrf = CSRFProtect()


def create_app(test_config=None):
    load_dotenv()

    app = Flask(__name__, instance_relative_config=False)

    default_upload = os.path.abspath(os.path.join(app.root_path, "..", "uploads"))

    app.config.from_mapping(
        SECRET_KEY=os.environ.get("SECRET_KEY", "change-me"),
        SQLALCHEMY_DATABASE_URI=os.environ.get(
            "DATABASE_URL",
            "sqlite:///crece_portal.db",
        ),
        SQLALCHEMY_TRACK_MODIFICATIONS=False,
        UPLOAD_FOLDER=os.environ.get("UPLOAD_FOLDER", default_upload),
        MAX_CONTENT_LENGTH=2 * 1024 * 1024 * 1024,
        SECURITY_ENFORCE_ACTIVE_USERS=True,
    )

    if test_config:
        app.config.update(test_config)

    os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)

    db.init_app(app)
    migrate.init_app(app, db)
    csrf.init_app(app)

    login_manager.init_app(app)
    login_manager.login_view = "auth.login"
    login_manager.login_message = "Por favor inicia sesión para continuar."

    from .blueprints.auth import auth_bp
    from .blueprints.datasets import datasets_bp
    from .blueprints.admin import admin_bp

    app.register_blueprint(auth_bp)
    app.register_blueprint(datasets_bp)
    app.register_blueprint(admin_bp)

    from .cli import register_cli

    register_cli(app)

    @login_manager.user_loader
    def load_user(user_id):
        return User.query.get(int(user_id))

    @app.context_processor
    def inject_globals():
        from datetime import datetime

        return {"current_year": datetime.utcnow().year}

    return app
