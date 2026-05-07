from django import forms
from .models import Oportunidade


class OportunidadeForm(forms.ModelForm):
    """Formulário para criar/editar oportunidades."""
    
    class Meta:
        model = Oportunidade
        fields = [
            'titulo',
            'descricao',
            'categoria',
            'bairro_nome',
            'valor_faixa',
            'whatsapp_contato',
        ]
        widgets = {
            'titulo': forms.TextInput(attrs={
                'placeholder': 'Título da vaga (ex: Preciso de Eletricista)',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'descricao': forms.Textarea(attrs={
                'placeholder': 'Descreva a oportunidade com detalhes...',
                'rows': 6,
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'categoria': forms.Select(attrs={
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'bairro_nome': forms.TextInput(attrs={
                'placeholder': 'Bairro onde é a vaga',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'valor_faixa': forms.TextInput(attrs={
                'placeholder': 'Faixa salarial ou valor (ex: R$ 1500-2000, A combinar)',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
            'whatsapp_contato': forms.TextInput(attrs={
                'placeholder': 'WhatsApp para contato (deixe em branco para usar o seu)',
                'class': 'w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent'
            }),
        }
        labels = {
            'titulo': 'Título da Vaga',
            'descricao': 'Descrição',
            'categoria': 'Categoria',
            'bairro_nome': 'Bairro',
            'valor_faixa': 'Valor/Faixa Salarial',
            'whatsapp_contato': 'WhatsApp para Contato (opcional)',
        }
