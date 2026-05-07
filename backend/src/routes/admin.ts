import express, { Request, Response } from 'express';
import pool from '../database';
import jwt from 'jsonwebtoken';

const router = express.Router();

// Middleware to verify admin token
const verifyAdmin = (req: Request, res: Response, next: Function) => {
  const token = req.headers.authorization?.split(' ')[1];
  
  if (!token) {
    return res.status(401).json({ error: 'Token não fornecido' });
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET || 'secret');
    if ((decoded as any).role !== 'admin') {
      return res.status(403).json({ error: 'Acesso restrito a administradores' });
    }
    (req as any).user = decoded;
    next();
  } catch (error) {
    return res.status(403).json({ error: 'Token inválido' });
  }
};

// Get all opportunities (including pending)
router.get('/', verifyAdmin, async (req: Request, res: Response) => {
  try {
    const result = await pool.query('SELECT * FROM opportunities ORDER BY created_at DESC');
    res.json(result.rows);
  } catch (error) {
    console.error('Get all opportunities error:', error);
    res.status(500).json({ error: 'Erro ao buscar oportunidades' });
  }
});

// Approve opportunity
router.put('/:id/approve', verifyAdmin, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    const result = await pool.query(
      'UPDATE opportunities SET is_approved = true WHERE id = $1 RETURNING *',
      [id]
    );
    
    if (result.rows.length === 0) {
      return res.status(404).json({ error: 'Oportunidade não encontrada' });
    }

    res.json(result.rows[0]);
  } catch (error) {
    console.error('Approve opportunity error:', error);
    res.status(500).json({ error: 'Erro ao aprovar oportunidade' });
  }
});

// Reject/Delete opportunity
router.delete('/:id', verifyAdmin, async (req: Request, res: Response) => {
  try {
    const { id } = req.params;
    await pool.query('DELETE FROM opportunities WHERE id = $1', [id]);
    res.json({ message: 'Oportunidade removida com sucesso' });
  } catch (error) {
    console.error('Delete opportunity error:', error);
    res.status(500).json({ error: 'Erro ao remover oportunidade' });
  }
});

// Get statistics
router.get('/stats', verifyAdmin, async (req: Request, res: Response) => {
  try {
    const totalQuery = await pool.query('SELECT COUNT(*) FROM opportunities');
    const approvedQuery = await pool.query('SELECT COUNT(*) FROM opportunities WHERE is_approved = true');
    const pendingQuery = await pool.query('SELECT COUNT(*) FROM opportunities WHERE is_approved = false');
    const usersQuery = await pool.query('SELECT COUNT(*) FROM users');

    res.json({
      total: parseInt(totalQuery.rows[0].count),
      approved: parseInt(approvedQuery.rows[0].count),
      pending: parseInt(pendingQuery.rows[0].count),
      users: parseInt(usersQuery.rows[0].count),
    });
  } catch (error) {
    console.error('Get stats error:', error);
    res.status(500).json({ error: 'Erro ao buscar estatísticas' });
  }
});

export default router;
