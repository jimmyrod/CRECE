from flask_wtf import FlaskForm
from wtforms import (
    BooleanField,
    FileField,
    PasswordField,
    SelectField,
    StringField,
    TextAreaField,
)
from wtforms.validators import Email, EqualTo, InputRequired, Length, Optional

class RegistrationForm(FlaskForm):
    first_name = StringField("Nombre", validators=[InputRequired(), Length(max=100)])
    last_name = StringField("Apellido", validators=[InputRequired(), Length(max=100)])
    email = StringField("Correo electrónico", validators=[InputRequired(), Email(), Length(max=190)])
    password = PasswordField("Contraseña", validators=[InputRequired(), Length(min=8, max=128)])
    confirm_password = PasswordField(
        "Confirmar contraseña",
        validators=[InputRequired(), EqualTo("password", message="Las contraseñas no coinciden")],
    )
    institution = StringField("Institución", validators=[Optional(), Length(max=190)])
    country = StringField("País", validators=[Optional(), Length(max=100)])
    orcid = StringField("ORCID", validators=[Optional(), Length(max=32)])
    phone_number = StringField("Teléfono", validators=[Optional(), Length(max=30)])
    accept_terms = BooleanField("Acepto las políticas de uso", validators=[InputRequired()])


class LoginForm(FlaskForm):
    email = StringField("Correo electrónico", validators=[InputRequired(), Email(), Length(max=190)])
    password = PasswordField("Contraseña", validators=[InputRequired()])
    remember = BooleanField("Recordarme")


class DatasetForm(FlaskForm):
    title = StringField("Título", validators=[InputRequired(), Length(max=255)])
    slug = StringField("Identificador", validators=[InputRequired(), Length(max=160)])
    summary = TextAreaField("Resumen", validators=[InputRequired()])
    keywords = TextAreaField("Palabras clave", validators=[Optional()])
    category = StringField("Categoría", validators=[Optional(), Length(max=120)])
    geographic_scope = StringField("Alcance geográfico", validators=[Optional(), Length(max=120)])
    publication_year = StringField("Año de publicación", validators=[Optional(), Length(max=4)])
    contact_name = StringField("Contacto", validators=[Optional(), Length(max=190)])
    contact_email = StringField("Correo de contacto", validators=[Optional(), Email(), Length(max=190)])
    legal_restrictions = TextAreaField("Restricciones legales", validators=[Optional()])
    visibility = SelectField(
        "Visibilidad",
        choices=[("public", "Público"), ("internal", "Solo Fundación"), ("restricted", "Restringido")],
        validators=[InputRequired()],
    )
    default_access_level = SelectField(
        "Nivel de acceso por defecto",
        choices=[("preview", "Vista previa"), ("download", "Descarga")],
        validators=[InputRequired()],
    )


class DatasetVersionForm(FlaskForm):
    version_label = StringField("Versión", validators=[InputRequired(), Length(max=50)])
    file = FileField("Archivo principal", validators=[InputRequired()])
    file_format = SelectField(
        "Formato",
        choices=[("csv", "CSV"), ("dta", "Stata"), ("rds", "RDS"), ("zip", "ZIP"), ("other", "Otro")],
        validators=[InputRequired()],
    )
    change_log = TextAreaField("Notas de cambios", validators=[Optional()])


class DocumentationUploadForm(FlaskForm):
    file_label = StringField("Nombre del archivo", validators=[InputRequired(), Length(max=150)])
    file_format = SelectField(
        "Tipo",
        choices=[
            ("documentation", "Documentación"),
            ("codebook", "Codebook"),
            ("csv", "CSV"),
            ("dta", "Stata"),
            ("rds", "RDS"),
            ("other", "Otro"),
        ],
        validators=[InputRequired()],
    )
    file = FileField("Archivo", validators=[InputRequired()])


class AccessRequestForm(FlaskForm):
    intended_use = TextAreaField("Uso previsto", validators=[InputRequired(), Length(min=20)])
    methodology = TextAreaField("Metodología", validators=[Optional()])
    institution = StringField("Institución", validators=[Optional(), Length(max=190)])
    expected_publication = TextAreaField("Resultado esperado", validators=[Optional()])
    safeguards = TextAreaField("Salvaguardas", validators=[Optional()])
    agreement_version = StringField("Versión del acuerdo", validators=[Optional(), Length(max=50)])


class ReviewDecisionForm(FlaskForm):
    decision = SelectField(
        "Decisión",
        choices=[
            ("approved", "Aprobar"),
            ("rejected", "Rechazar"),
            ("needs_more_info", "Solicitar más información"),
        ],
        validators=[InputRequired()],
    )
    decision_notes = TextAreaField("Notas", validators=[Optional()])
