from django.shortcuts import render, get_object_or_404, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.core.paginator import Paginator
from django.db.models import Q
from django.http import JsonResponse
from .models import Oportunidade, Categoria
from .forms import OportunidadeForm


def home_view(request):
    """Página inicial com listagem de oportunidades em destaque."""
    oportunidades = Oportunidade.objects.filter(status='aprovado').select_related('categoria')[:8]
    categorias = Categoria.objects.all()
    
    context = {
        'oportunidades': oportunidades,
        'categorias': categorias,
    }
    return render(request, 'core/home.html', context)


def lista_oportunidades_view(request):
    """Listagem completa de oportunidades com filtros."""
    queryset = Oportunidade.objects.filter(status='aprovado').select_related('categoria')
    
    # Filtros
    categoria_slug = request.GET.get('categoria')
    bairro = request.GET.get('bairro')
    busca = request.GET.get('busca')
    
    if categoria_slug:
        queryset = queryset.filter(categoria__slug=categoria_slug)
    
    if bairro:
        queryset = queryset.filter(bairro_nome__icontains=bairro)
    
    if busca:
        queryset = queryset.filter(
            Q(titulo__icontains=busca) | 
            Q(descricao__icontains=busca)
        )
    
    # Paginação
    paginator = Paginator(queryset, 12)
    page_number = request.GET.get('page')
    page_obj = paginator.get_page(page_number)
    
    # Para HTMX
    if request.headers.get('HX-Request'):
        return render(request, 'jobs/partials/oportunidades_grid.html', {
            'page_obj': page_obj,
        })
    
    categorias = Categoria.objects.all()
    
    context = {
        'page_obj': page_obj,
        'categorias': categorias,
        'filtros': {
            'categoria': categoria_slug,
            'bairro': bairro,
            'busca': busca,
        }
    }
    return render(request, 'jobs/list.html', context)


def detalhe_oportunidade_view(request, slug):
    """Detalhe de uma oportunidade."""
    oportunidade = get_object_or_404(
        Oportunidade.objects.select_related('autor', 'categoria'),
        slug=slug,
        status='aprovado'
    )
    
    # Oportunidades relacionadas
    relacionadas = Oportunidade.objects.filter(
        categoria=oportunidade.categoria,
        status='aprovado'
    ).exclude(pk=oportunidade.pk)[:4]
    
    context = {
        'oportunidade': oportunidade,
        'relacionadas': relacionadas,
    }
    return render(request, 'jobs/detail.html', context)


@login_required
def criar_oportunidade_view(request):
    """Criar nova oportunidade."""
    if request.user.user_type != 'contratante':
        messages.error(request, 'Apenas contratantes podem publicar vagas.')
        return redirect('dashboard')
    
    if request.method == 'POST':
        form = OportunidadeForm(request.POST)
        if form.is_valid():
            oportunidade = form.save(commit=False)
            oportunidade.autor = request.user
            oportunidade.save()
            messages.success(
                request, 
                'Oportunidade criada! Ela será revisada e publicada em breve.'
            )
            return redirect('dashboard')
    else:
        form = OportunidadeForm()
    
    context = {'form': form}
    return render(request, 'jobs/form.html', context)


@login_required
def editar_oportunidade_view(request, slug):
    """Editar oportunidade."""
    oportunidade = get_object_or_404(Oportunidade, slug=slug, autor=request.user)
    
    if request.method == 'POST':
        form = OportunidadeForm(request.POST, instance=oportunidade)
        if form.is_valid():
            form.save()
            messages.success(request, 'Oportunidade atualizada com sucesso!')
            return redirect('dashboard')
    else:
        form = OportunidadeForm(instance=oportunidade)
    
    context = {'form': form, 'oportunidade': oportunidade}
    return render(request, 'jobs/form.html', context)


@login_required
def excluir_oportunidade_view(request, slug):
    """Excluir oportunidade."""
    oportunidade = get_object_or_404(Oportunidade, slug=slug, autor=request.user)
    
    if request.method == 'POST':
        oportunidade.delete()
        messages.success(request, 'Oportunidade excluída com sucesso!')
        return redirect('dashboard')
    
    context = {'oportunidade': oportunidade}
    return render(request, 'jobs/confirm_delete.html', context)


def mapa_oportunidades_view(request):
    """API JSON para o mapa de oportunidades."""
    oportunidades = Oportunidade.objects.filter(
        status='aprovado'
    ).select_related('categoria')[:50]
    
    data = []
    for opp in oportunidades:
        data.append({
            'titulo': opp.titulo,
            'bairro': opp.bairro_nome,
            'categoria': opp.categoria.nome if opp.categoria else 'Outros',
            'created_at': opp.created_at.strftime('%d/%m/%Y'),
        })
    
    return JsonResponse({'oportunidades': data})
