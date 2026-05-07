import express, { Request, Response } from 'express';
import jwt from 'jsonwebtoken';
import pool from '../database';

const router = express.Router();

// Middleware to verify token
const verifyToken = (req: Request, res: Response, next: Function) => {
  const token = req.headers.authorization?.split(' ')[1];
  
  if (!token) {
    return res.status(401).json({ error: 'Token não fornecido' });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'secret');
    (req as any).user = decoded;
    next();
  } catch (error) {
    return res.status(403).json({ error: 'Token inválido' });
  }
};

// Get all opportunities (public)
router.get('/', async (req: Request, res: Response) => {
  try {
    const { category, neighborhood, search } = req.query;
    
    let query = 'SELECT * FROM opportunities WHERE is_approved = true';
    const values: any[] = [];
    let paramCount = 1;

    if (category) {
      query += ` AND category = $${paramCount}`;
      values.push(category);
      paramCount++;
    }

    if (neighborhood) {
      query += ` AND neighborhood = $${paramCount}`;
      values.push(neighborhood);
      paramCount++;
    }

    if (search) {
      query += ` AND (title ILIKE $${paramCount} OR description ILIKE $${paramCount} OR company ILIKE $${paramCount})`;
      values.push(`%${search}%`);
      paramCount++;
    }

    query += ' ORDER BY created_at DESC';

    const result = await pool.query(query, values);
    res.json(result.rows);
  } catch (error) {
    console.error('Get opportunities error:', error);
    res.status(500).json({ error: 'Erro ao buscar oportunidades' });
  }
});

// Get opportunity by ID
router.get('/:id', async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const result = await pool.query('SELECT * FROM opportunities WHERE id = $1', [id]);
    
    if (result.rows.length === 0) {
      return res.status(404).json({ error: 'Oportunidade não encontrada' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Get opportunity error:', error);
    res.status(500).json({ error: 'Erro ao buscar oportunidade' });
  }
});

// Create opportunity (protected)
router.post('/', verifyToken, async (req: Request, res: Response) => {
  try {
    const { title, description, company, whatsapp, category, neighborhood, salary } = req.body;
    const userId = (req as any).user.id;

    if (!title || !description || !company || !whatsapp || !category || !neighborhood) {
      return res.status(400).json({ error: 'Todos os campos obrigatórios devem ser preenchidos' });
    }

    const result = await pool.query(
      `INSERT INTO opportunities (title, description, company, whatsapp, category, neighborhood, salary, user_id)
       VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
       RETURNING *`,
      [title, description, company, whatsapp, category, neighborhood, salary || null, userId]
    );

    res.status(201).json(result.rows[0]);
  } catch (error) {
    console.error('Create opportunity error:', error);
    res.status(500).json({ error: 'Erro ao criar oportunidade' });
  }
});

// Get user's opportunities (protected)
router.get('/user/me', verifyToken, async (req: Request, res: Response) => {
  try {
    const userId = (req as any).user.id;
    const result = await pool.query(
      'SELECT * FROM opportunities WHERE user_id = $1 ORDER BY created_at DESC',
      [userId]
    );
    res.json(result.rows);
  } catch (error) {
    console.error('Get user opportunities error:', error);
    res.status(500).json({ error: 'Erro ao buscar oportunidades' });
  }
});

// Update opportunity (protected - owner only)
router.put('/:id', verifyToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const userId = (req as any).user.id;
    const { title, description, company, whatsapp, category, neighborhood, salary } = req.body;

    // Check ownership
    const existing = await pool.query('SELECT * FROM opportunities WHERE id = $1', [id]);
    if (existing.rows.length === 0) {
      return res.status(404).json({ error: 'Oportunidade não encontrada' });
    }

    if (existing.rows[0].user_id !== userId && (req as any).user.role !== 'admin') {
      return res.status(403).json({ error: 'Não autorizado' });
    }

    const result = await pool.query(
      `UPDATE opportunities 
       SET title = $1, description = $2, company = $3, whatsapp = $4, category = $5, neighborhood = $6, salary = $7
       WHERE id = $8
       RETURNING *`,
      [title, description, company, whatsapp, category, neighborhood, salary, id]
    );

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Update opportunity error:', error);
    res.status(500).json({ error: 'Erro ao atualizar oportunidade' });
  }
});

// Delete opportunity (protected - owner or admin)
router.delete('/:id', verifyToken, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const userId = (req as any).user.id;

    // Check ownership
    const existing = await pool.query('SELECT * FROM opportunities WHERE id = $1', [id]);
    if (existing.rows.length === 0) {
      return res.status(404).json({ error: 'Oportunidade não encontrada' });
    }

    if (existing.rows[0].user_id !== userId && (req as any).user.role !== 'admin') {
      return res.status(403).json({ error: 'Não autorizado' });
    }

    await pool.query('DELETE FROM opportunities WHERE id = $1', [id]);
    res.json({ message: 'Oportunidade removida com sucesso' });
  } catch (error) {
    console.error('Delete opportunity error:', error);
    res.status(500).json({ error: 'Erro ao remover oportunidade' });
  }
});

export default router;
