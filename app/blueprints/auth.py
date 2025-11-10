from flask import Blueprint, flash, redirect, render_template, request, url_for
from flask_login import current_user, login_required, login_user, logout_user

from ..extensions import db
from ..forms import LoginForm, RegistrationForm
from ..models import User


auth_bp = Blueprint("auth", __name__, url_prefix="/auth")


@auth_bp.route("/register", methods=["GET", "POST"])
def register():
    if current_user.is_authenticated:
        return redirect(url_for("datasets.index"))

    form = RegistrationForm()
    if form.validate_on_submit():
        if User.query.filter_by(email=form.email.data.lower()).first():
            form.email.errors.append("Ya existe una cuenta con ese correo electrónico")
        else:
            user = User(
                first_name=form.first_name.data.strip(),
                last_name=form.last_name.data.strip(),
                email=form.email.data.strip().lower(),
                institution=form.institution.data,
                country=form.country.data,
                orcid=form.orcid.data,
                phone_number=form.phone_number.data,
            )
            user.set_password(form.password.data)
            db.session.add(user)
            db.session.commit()
            flash(
                "Registro exitoso. Recibirás una notificación cuando tu cuenta sea activada.",
                "success",
            )
            return redirect(url_for("auth.login"))
    return render_template("auth/register.html", form=form)


@auth_bp.route("/login", methods=["GET", "POST"])
def login():
    if current_user.is_authenticated:
        return redirect(url_for("datasets.index"))

    form = LoginForm()
    if form.validate_on_submit():
        user = User.query.filter_by(email=form.email.data.lower()).first()
        if user and user.check_password(form.password.data):
            if user.status != "active":
                flash("Tu cuenta aún no ha sido activada.", "warning")
            else:
                login_user(user, remember=form.remember.data)
                flash("Bienvenido nuevamente", "success")
                next_url = request.args.get("next")
                return redirect(next_url or url_for("datasets.index"))
        else:
            flash("Credenciales inválidas", "danger")
    return render_template("auth/login.html", form=form)


@auth_bp.route("/logout")
@login_required
def logout():
    logout_user()
    flash("Sesión cerrada correctamente", "info")
    return redirect(url_for("datasets.index"))
