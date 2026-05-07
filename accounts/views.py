from django.shortcuts import render, redirect
from django.contrib.auth import login, logout, authenticate
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.views.generic import CreateView, UpdateView
from django.urls import reverse_lazy
from .forms import CustomUserCreationForm, CustomAuthenticationForm
from .models import CustomUser


def register_view(request):
    """View de registro de usuário."""
    if request.user.is_authenticated:
        return redirect('dashboard')
    
    if request.method == 'POST':
        form = CustomUserCreationForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            messages.success(request, 'Conta criada com sucesso! Bem-vindo ao Oportunidades Belém.')
            return redirect('dashboard')
    else:
        form = CustomUserCreationForm()
    
    return render(request, 'accounts/register.html', {'form': form})


def login_view(request):
    """View de login."""
    if request.user.is_authenticated:
        return redirect('dashboard')
    
    if request.method == 'POST':
        form = CustomAuthenticationForm(request, data=request.POST)
        if form.is_valid():
            username = form.cleaned_data.get('username')
            password = form.cleaned_data.get('password')
            user = authenticate(username=username, password=password)
            if user is not None:
                login(request, user)
                messages.success(request, f'Bem-vindo de volta, {user.first_name or user.username}!')
                next_url = request.GET.get('next', 'dashboard')
                return redirect(next_url)
    else:
        form = CustomAuthenticationForm()
    
    return render(request, 'accounts/login.html', {'form': form})


@login_required
def logout_view(request):
    """View de logout."""
    logout(request)
    messages.info(request, 'Você saiu da sua conta.')
    return redirect('home')


@login_required
def dashboard_view(request):
    """Dashboard do usuário."""
    oportunidades = request.user.oportunidades.all().order_by('-created_at')
    
    context = {
        'oportunidades': oportunidades,
        'pending_count': oportunidades.filter(status='pendente').count(),
        'approved_count': oportunidades.filter(status='aprovado').count(),
    }
    
    return render(request, 'accounts/dashboard.html', context)


@login_required
def profile_update_view(request):
    """Atualizar perfil do usuário."""
    if request.method == 'POST':
        # Implementar atualização de perfil
        messages.success(request, 'Perfil atualizado com sucesso!')
        return redirect('dashboard')
    
    return render(request, 'accounts/profile.html')
