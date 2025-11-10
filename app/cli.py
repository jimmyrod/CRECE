import click

from .extensions import db
from .models import User


def register_cli(app):
    @app.cli.command("create-admin")
    @click.argument("email")
    @click.option("--password", prompt=True, hide_input=True, confirmation_prompt=True)
    @click.option("--first-name", prompt=True)
    @click.option("--last-name", prompt=True)
    def create_admin(email, password, first_name, last_name):
        """Create an administrator user."""
        if User.query.filter_by(email=email).first():
            click.echo("Ya existe un usuario con ese correo electrónico.")
            return

        user = User(
            email=email,
            first_name=first_name,
            last_name=last_name,
            role="administrator",
            status="active",
        )
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        click.echo("Usuario administrador creado correctamente.")

    return app
