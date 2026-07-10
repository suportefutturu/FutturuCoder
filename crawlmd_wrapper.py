#!/usr/bin/env python3
"""
CrawlMD Wrapper com Comportamento Humano

Este script atua como um wrapper para o comando `crawlmd`, adicionando:
- Delay aleatório entre requisições (simulação humana)
- Tratamento de erros com backoff exponencial
- Headers realistas (quando usando modo simulado)
- Logs detalhados de cada ação

Uso:
    python crawlmd_wrapper.py <url> [--mode crawlmd|simulate] [--min-delay 2] [--max-delay 5]

Autor: Assistant
Licença: MIT
"""

import subprocess
import time
import random
import sys
import argparse
from typing import Optional, Tuple


class HumanLikeCrawler:
    """
    Classe principal que gerencia o crawling com comportamento humano.
    
    Atributos:
        min_delay (float): Tempo mínimo de espera entre requisições (segundos)
        max_delay (float): Tempo máximo de espera entre requisições (segundos)
        max_retries (int): Número máximo de tentativas antes de desistir
        base_backoff (float): Tempo base para backoff exponencial (segundos)
    """
    
    def __init__(
        self,
        min_delay: float = 2.0,
        max_delay: float = 5.0,
        max_retries: int = 3,
        base_backoff: float = 10.0
    ):
        self.min_delay = min_delay
        self.max_delay = max_delay
        self.max_retries = max_retries
        self.base_backoff = base_backoff
        
        # Headers realistas que simulam um navegador moderno
        self.headers = {
            'User-Agent': (
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
                'AppleWebKit/537.36 (KHTML, like Gecko) '
                'Chrome/120.0.0.0 Safari/537.36'
            ),
            'Accept': (
                'text/html,application/xhtml+xml,application/xml;'
                'q=0.9,image/avif,image/webp,image/apng,*/*;'
                'q=0.8,application/signed-exchange;v=b3;q=0.7'
            ),
            'Accept-Language': 'pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding': 'gzip, deflate, br',
            'Connection': 'keep-alive',
            'Upgrade-Insecure-Requests': '1',
            'Sec-Ch-Ua': '"Not_A Brand";v="8", "Chromium";v="120"',
            'Sec-Ch-Ua-Mobile': '?0',
            'Sec-Ch-Ua-Platform': '"Windows"',
            'Sec-Fetch-Dest': 'document',
            'Sec-Fetch-Mode': 'navigate',
            'Sec-Fetch-Site': 'none',
            'Cache-Control': 'max-age=0',
        }
        
        print("=" * 60)
        print("CrawlMD Wrapper - Modo Humano")
        print("=" * 60)
        print(f"Delay entre requisições: {min_delay}s - {max_delay}s")
        print(f"Máximo de retries: {max_retries}")
        print(f"Backoff base: {base_backoff}s")
        print("=" * 60)
    
    def human_delay(self, context: str = "entre requisições") -> None:
        """
        Aplica um delay aleatório para simular comportamento humano.
        
        Args:
            context: Descrição do contexto do delay para logging
        """
        delay = random.uniform(self.min_delay, self.max_delay)
        print(f"[INFO] Esperando {delay:.2f}s {context}...")
        time.sleep(delay)
    
    def exponential_backoff(self, attempt: int, error_type: str) -> float:
        """
        Calcula o tempo de espera com backoff exponencial após erro.
        
        Args:
            attempt: Número da tentativa atual (0-based)
            error_type: Tipo do erro ocorrido
            
        Returns:
            float: Tempo de espera em segundos
        """
        # Backoff exponencial: base_backoff * 2^attempt + jitter aleatório
        backoff_time = self.base_backoff * (2 ** attempt)
        jitter = random.uniform(0, backoff_time * 0.1)  # 10% de jitter
        total_wait = backoff_time + jitter
        
        print(
            f"[ERRO] {error_type} detectado. "
            f"Tentativa {attempt + 1}/{self.max_retries}. "
            f"Aguardando {total_wait:.2f}s..."
        )
        time.sleep(total_wait)
        
        return total_wait
    
    def check_crawlmd_available(self) -> bool:
        """
        Verifica se o comando `crawlmd` está disponível no sistema.
        
        Returns:
            bool: True se disponível, False caso contrário
        """
        try:
            result = subprocess.run(
                ['which', 'crawlmd'],
                capture_output=True,
                text=True,
                timeout=5
            )
            available = result.returncode == 0
            if available:
                print(f"[INFO] crawlmd encontrado em: {result.stdout.strip()}")
            else:
                print("[AVISO] crawlmd não encontrado no PATH")
            return available
        except subprocess.TimeoutExpired:
            print("[ERRO] Timeout ao verificar crawlmd")
            return False
        except Exception as e:
            print(f"[ERRO] Erro ao verificar crawlmd: {e}")
            return False
    
    def execute_crawlmd(self, url: str) -> Tuple[bool, Optional[str]]:
        """
        Executa o comando `crawlmd` com a URL especificada.
        
        Args:
            url: URL a ser processada pelo crawlmd
            
        Returns:
            Tuple[bool, Optional[str]]: (sucesso, output ou erro)
        """
        try:
            print(f"[INFO] Executando crawlmd para: {url}")
            
            # Executa o comando crawlmd
            # Nota: Ajuste os argumentos conforme necessário para seu caso de uso
            result = subprocess.run(
                ['crawlmd', url],
                capture_output=True,
                text=True,
                timeout=300,  # 5 minutos de timeout
                env={**subprocess.os.environ}  # Herda variáveis de ambiente
            )
            
            if result.returncode == 0:
                print(f"[SUCESSO] crawlmd completado com sucesso")
                return True, result.stdout
            else:
                error_msg = f"crawlmd falhou com código {result.returncode}"
                if result.stderr:
                    error_msg += f": {result.stderr.strip()}"
                print(f"[ERRO] {error_msg}")
                return False, result.stderr
                
        except subprocess.TimeoutExpired:
            error_msg = "Timeout na execução do crawlmd"
            print(f"[ERRO] {error_msg}")
            return False, error_msg
        except FileNotFoundError:
            error_msg = "Comando crawlmd não encontrado"
            print(f"[ERRO] {error_msg}")
            return False, error_msg
        except Exception as e:
            error_msg = f"Erro inesperado: {str(e)}"
            print(f"[ERRO] {error_msg}")
            return False, error_msg
    
    def simulate_request(self, url: str) -> Tuple[bool, Optional[str]]:
        """
        Simula uma requisição HTTP com headers realistas (modo de teste).
        
        Este método é usado quando o crawlmd não está disponível ou para testes.
        
        Args:
            url: URL a ser requisitada
            
        Returns:
            Tuple[bool, Optional[str]]: (sucesso, conteúdo ou erro)
        """
        try:
            # Tenta importar requests, se não estiver disponível usa urllib
            try:
                import requests
                print(f"[INFO] Enviando requisição (requests) para: {url}")
                
                response = requests.get(
                    url,
                    headers=self.headers,
                    timeout=30,
                    allow_redirects=True
                )
                
                # Verifica códigos de erro comuns
                if response.status_code == 429:
                    raise Exception("HTTP 429 - Too Many Requests")
                elif response.status_code == 403:
                    raise Exception("HTTP 403 - Forbidden")
                elif response.status_code >= 400:
                    raise Exception(f"HTTP {response.status_code}")
                
                print(f"[SUCESSO] Requisição completada (status {response.status_code})")
                return True, response.text[:500]  # Retorna apenas preview
                
            except ImportError:
                # Fallback para urllib (biblioteca padrão)
                import urllib.request
                import urllib.error
                
                print(f"[INFO] Enviando requisição (urllib) para: {url}")
                
                req = urllib.request.Request(url, headers=self.headers)
                
                with urllib.request.urlopen(req, timeout=30) as response:
                    content = response.read().decode('utf-8')
                    print(f"[SUCESSO] Requisição completada")
                    return True, content[:500]
                    
        except Exception as e:
            error_msg = str(e)
            print(f"[ERRO] Falha na requisição: {error_msg}")
            return False, error_msg
    
    def crawl_with_retry(
        self,
        url: str,
        mode: str = 'crawlmd'
    ) -> Tuple[bool, Optional[str]]:
        """
        Executa o crawling com retry e backoff exponencial.
        
        Args:
            url: URL a ser processada
            mode: 'crawlmd' para usar o comando real, 'simulate' para simulação
            
        Returns:
            Tuple[bool, Optional[str]]: (sucesso, resultado ou erro)
        """
        attempt = 0
        
        while attempt < self.max_retries:
            print(f"\n{'=' * 60}")
            print(f"Tentativa {attempt + 1}/{self.max_retries}")
            print(f"{'=' * 60}")
            
            # Executa a ação apropriada baseada no modo
            if mode == 'crawlmd':
                success, result = self.execute_crawlmd(url)
            else:
                success, result = self.simulate_request(url)
            
            if success:
                # Aplica delay humano após sucesso
                self.human_delay("após requisição bem-sucedida")
                return True, result
            
            # Falhou - aplica backoff exponencial
            error_type = "Erro de requisição" if mode == 'simulate' else "Erro do crawlmd"
            if attempt < self.max_retries - 1:  # Não faz backoff na última tentativa
                self.exponential_backoff(attempt, error_type)
            
            attempt += 1
        
        # Todas as tentativas falharam
        print(f"\n[CRÍTICO] Todas as {self.max_retries} tentativas falharam para {url}")
        return False, None
    
    def crawl_multiple_urls(
        self,
        urls: list,
        mode: str = 'crawlmd'
    ) -> dict:
        """
        Processa múltiplas URLs com comportamento humano entre cada uma.
        
        Args:
            urls: Lista de URLs para processar
            mode: 'crawlmd' ou 'simulate'
            
        Returns:
            dict: Resultados do processamento {url: (sucesso, resultado)}
        """
        results = {}
        
        print(f"\n[INFO] Iniciando processamento de {len(urls)} URL(s)...")
        
        for i, url in enumerate(urls, 1):
            print(f"\n{'#' * 60}")
            print(f"Processando URL {i}/{len(urls)}: {url}")
            print(f"{'#' * 60}")
            
            success, result = self.crawl_with_retry(url, mode)
            results[url] = (success, result)
            
            # Delay humano entre URLs (se não for a última)
            if i < len(urls):
                self.human_delay("antes da próxima URL")
        
        # Resumo final
        print(f"\n{'=' * 60}")
        print("RESUMO DO PROCESSAMENTO")
        print(f"{'=' * 60}")
        successful = sum(1 for success, _ in results.values() if success)
        failed = len(urls) - successful
        print(f"Total: {len(urls)} URLs")
        print(f"Sucesso: {successful}")
        print(f"Falhas: {failed}")
        print(f"{'=' * 60}\n")
        
        return results


def main():
    """Função principal com parsing de argumentos."""
    
    parser = argparse.ArgumentParser(
        description='CrawlMD Wrapper com comportamento humano',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Exemplos de uso:
  %(prog)s https://example.com
  %(prog)s https://example.com --mode simulate
  %(prog)s https://example.com https://test.com --min-delay 3 --max-delay 7
  %(prog)s https://example.com --max-retries 5 --base-backoff 15
        """
    )
    
    parser.add_argument(
        'urls',
        nargs='+',
        help='URL(s) para processar'
    )
    
    parser.add_argument(
        '--mode',
        choices=['crawlmd', 'simulate'],
        default='crawlmd',
        help='Modo de operação: crawlmd (comando real) ou simulate (simulação com requests)'
    )
    
    parser.add_argument(
        '--min-delay',
        type=float,
        default=2.0,
        help='Tempo mínimo de delay entre requisições (segundos). Default: 2.0'
    )
    
    parser.add_argument(
        '--max-delay',
        type=float,
        default=5.0,
        help='Tempo máximo de delay entre requisições (segundos). Default: 5.0'
    )
    
    parser.add_argument(
        '--max-retries',
        type=int,
        default=3,
        help='Número máximo de tentativas por URL. Default: 3'
    )
    
    parser.add_argument(
        '--base-backoff',
        type=float,
        default=10.0,
        help='Tempo base para backoff exponencial (segundos). Default: 10.0'
    )
    
    parser.add_argument(
        '--check-only',
        action='store_true',
        help='Apenas verifica se crawlmd está disponível e sai'
    )
    
    args = parser.parse_args()
    
    # Validação dos parâmetros
    if args.min_delay < 0 or args.max_delay < 0:
        print("[ERRO] Delays não podem ser negativos")
        sys.exit(1)
    
    if args.min_delay > args.max_delay:
        print("[ERRO] min-delay não pode ser maior que max-delay")
        sys.exit(1)
    
    if args.max_retries < 1:
        print("[ERRO] max-retries deve ser pelo menos 1")
        sys.exit(1)
    
    if args.base_backoff < 0:
        print("[ERRO] base-backoff não pode ser negativo")
        sys.exit(1)
    
    # Cria instância do crawler
    crawler = HumanLikeCrawler(
        min_delay=args.min_delay,
        max_delay=args.max_delay,
        max_retries=args.max_retries,
        base_backoff=args.base_backoff
    )
    
    # Modo check-only
    if args.check_only:
        available = crawler.check_crawlmd_available()
        sys.exit(0 if available else 1)
    
    # Verifica disponibilidade do crawlmd se mode=crawlmd
    if args.mode == 'crawlmd':
        if not crawler.check_crawlmd_available():
            print("\n[AVISO] crawlmd não encontrado. Alternando para modo simulate...")
            args.mode = 'simulate'
    
    # Processa as URLs
    try:
        results = crawler.crawl_multiple_urls(args.urls, mode=args.mode)
        
        # Retorna código de saída baseado no sucesso
        all_success = all(success for success, _ in results.values())
        sys.exit(0 if all_success else 1)
        
    except KeyboardInterrupt:
        print("\n\n[INTERRUPÇÃO] Processo interrompido pelo usuário (Ctrl+C)")
        sys.exit(130)
    except Exception as e:
        print(f"\n[ERRO CRÍTICO] {str(e)}")
        sys.exit(1)


if __name__ == '__main__':
    main()
