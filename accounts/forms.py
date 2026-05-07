from django import forms
from django.contrib.auth.forms import UserCreationForm, AuthenticationForm
from .models import CustomUser


class CustomUserCreationForm(UserCreationForm):
    """Formulário de registro customizado."""
    
    whatsapp = forms.CharField(
        max_length=15,
        required=True,
        widget=forms.TextInput(attrs={
            'placeholder': '(91) 9XXXX-XXXX',
            'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
        }),
        help_text='WhatsApp com DDD (ex: 91987654321)'
    )
    
    user_type = forms.ChoiceField(
        choices=CustomUser.USER_TYPE_CHOICES,
        widget=forms.RadioSelect(attrs={'class': 'space-y-2'}),
        required=True,
        label='Tipo de Usuário'
    )
    
    bairro = forms.ChoiceField(
        choices=[('', 'Selecione seu bairro')] + list(CustomUser.BAIRRO_CHOICES),
        required=False,
        widget=forms.Select(attrs={
            'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
        })
    )
    
    class Meta(UserCreationForm.Meta):
        model = CustomUser
        fields = ('username', 'email', 'whatsapp', 'bairro', 'user_type', 'password1', 'password2')
        widgets = {
            'username': forms.TextInput(attrs={
                'placeholder': 'Nome de usuário',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'email': forms.EmailInput(attrs={
                'placeholder': 'seu@email.com',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
        }


class CustomAuthenticationForm(AuthenticationForm):
    """Formulário de login customizado."""
    
    username = forms.CharField(
        widget=forms.TextInput(attrs={
            'placeholder': 'Usuário ou email',
            'autofocus': True,
            'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
        })
    )
    
    password = forms.CharField(
        widget=forms.PasswordInput(attrs={
            'placeholder': 'Sua senha',
            'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
        })
    )
