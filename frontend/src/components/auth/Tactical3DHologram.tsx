import React, { useEffect, useRef } from 'react';

interface Tactical3DHologramProps {
  className?: string;
  size?: number;
}

export const Tactical3DHologram: React.FC<Tactical3DHologramProps> = ({ className }) => {
  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let animationFrameId: number;
    let width = (canvas.width = canvas.parentElement?.clientWidth || 400);
    let height = (canvas.height = canvas.parentElement?.clientHeight || 450);

    const handleResize = () => {
      if (!canvas || !canvas.parentElement) return;
      width = canvas.width = canvas.parentElement.clientWidth;
      height = canvas.height = canvas.parentElement.clientHeight;
    };
    window.addEventListener('resize', handleResize);

    // Mouse tracking for 3D camera tilt
    let mouseX = 0;
    let mouseY = 0;
    let targetRotX = 0.2;
    let targetRotY = 0;
    let currentRotX = 0.2;
    let currentRotY = 0;

    const handleMouseMove = (e: MouseEvent) => {
      const rect = canvas.getBoundingClientRect();
      const x = e.clientX - rect.left - rect.width / 2;
      const y = e.clientY - rect.top - rect.height / 2;
      mouseX = x / (rect.width / 2);
      mouseY = y / (rect.height / 2);
      targetRotY = mouseX * 0.7;
      targetRotX = -mouseY * 0.7 + 0.2;
    };
    window.addEventListener('mousemove', handleMouseMove);

    // 3D Geometry: Truncated Octahedron / Icosahedron Vertices
    const phi = (1 + Math.sqrt(5)) / 2;
    const rawVertices: [number, number, number][] = [
      [-1, phi, 0], [1, phi, 0], [-1, -phi, 0], [1, -phi, 0],
      [0, -1, phi], [0, 1, phi], [0, -1, -phi], [0, 1, -phi],
      [phi, 0, -1], [phi, 0, 1], [-phi, 0, -1], [-phi, 0, 1]
    ];

    // Scale vertices to core radius
    const coreRadius = 78;
    const coreVertices = rawVertices.map(([x, y, z]) => {
      const mag = Math.hypot(x, y, z);
      return [(x / mag) * coreRadius, (y / mag) * coreRadius, (z / mag) * coreRadius] as [number, number, number];
    });

    // Edges connecting vertices if distance matches standard edge
    const edges: [number, number][] = [];
    for (let i = 0; i < coreVertices.length; i++) {
      for (let j = i + 1; j < coreVertices.length; j++) {
        const [x1, y1, z1] = coreVertices[i];
        const [x2, y2, z2] = coreVertices[j];
        const d = Math.hypot(x2 - x1, y2 - y1, z2 - z1);
        if (d < coreRadius * 1.15) {
          edges.push([i, j]);
        }
      }
    }

    // 3D Particle Dust (80 particles drifting in 3D space)
    interface Particle3D {
      x: number;
      y: number;
      z: number;
      vx: number;
      vy: number;
      vz: number;
      size: number;
      alpha: number;
    }

    const particles: Particle3D[] = [];
    const particleCount = 75;
    for (let i = 0; i < particleCount; i++) {
      particles.push({
        x: (Math.random() - 0.5) * 440,
        y: (Math.random() - 0.5) * 440,
        z: (Math.random() - 0.5) * 350,
        vx: (Math.random() - 0.5) * 0.4,
        vy: (Math.random() - 0.5) * 0.4,
        vz: (Math.random() - 0.5) * 0.4,
        size: Math.random() * 2 + 1,
        alpha: Math.random() * 0.6 + 0.2,
      });
    }

    let time = 0;

    // Projection Function
    const project = (x: number, y: number, z: number, rX: number, rY: number, rZ = 0) => {
      // Rotation Z
      const x0 = x * Math.cos(rZ) - y * Math.sin(rZ);
      const y0 = x * Math.sin(rZ) + y * Math.cos(rZ);
      const z0 = z;

      // Rotation Y
      const x1 = x0 * Math.cos(rY) + z0 * Math.sin(rY);
      const z1 = -x0 * Math.sin(rY) + z0 * Math.cos(rY);

      // Rotation X
      const y2 = y0 * Math.cos(rX) - z1 * Math.sin(rX);
      const z2 = y0 * Math.sin(rX) + z1 * Math.cos(rX);

      // Perspective Projection
      const fov = 420;
      const cameraZ = 380;
      const scale = fov / (cameraZ + z2);
      const projX = width / 2 + x1 * scale;
      const projY = height / 2 + y2 * scale;

      return { x: projX, y: projY, z: z2, scale };
    };

    const render = () => {
      time += 0.016;

      // Smooth camera interpolation
      currentRotX += (targetRotX - currentRotX) * 0.06;
      currentRotY += (targetRotY - currentRotY) * 0.06;

      const baseRotY = currentRotY + time * 0.45;
      const baseRotX = currentRotX + Math.sin(time * 0.5) * 0.12;

      ctx.clearRect(0, 0, width, height);

      // Draw subtle background radar grid circles
      const centerX = width / 2;
      const centerY = height / 2;

      ctx.save();
      ctx.strokeStyle = 'rgba(163, 184, 121, 0.08)';
      ctx.lineWidth = 1;
      for (let r = 80; r <= 220; r += 50) {
        ctx.beginPath();
        ctx.arc(centerX, centerY, r, 0, Math.PI * 2);
        ctx.stroke();
      }

      // Radar scanning arm sweep
      const sweepAngle = time * 1.5;
      const gradient = ctx.createRadialGradient(centerX, centerY, 10, centerX, centerY, 210);
      gradient.addColorStop(0, 'rgba(163, 184, 121, 0.15)');
      gradient.addColorStop(1, 'rgba(163, 184, 121, 0.0)');

      ctx.beginPath();
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, 210, sweepAngle, sweepAngle + 0.4);
      ctx.closePath();
      ctx.fillStyle = gradient;
      ctx.fill();
      ctx.restore();

      // Render 3D Floating Particles
      for (const p of particles) {
        p.x += p.vx;
        p.y += p.vy;
        p.z += p.vz;

        if (p.x > 220) p.x = -220;
        if (p.x < -220) p.x = 220;
        if (p.y > 220) p.y = -220;
        if (p.y < -220) p.y = 220;
        if (p.z > 180) p.z = -180;
        if (p.z < -180) p.z = 180;

        const proj = project(p.x, p.y, p.z, baseRotX * 0.5, baseRotY * 0.5);
        if (proj.scale > 0) {
          ctx.beginPath();
          ctx.arc(proj.x, proj.y, p.size * proj.scale, 0, Math.PI * 2);
          const alpha = Math.max(0.05, Math.min(0.9, (p.alpha * proj.scale)));
          ctx.fillStyle = `rgba(163, 184, 121, ${alpha})`;
          ctx.fill();
        }
      }

      // Render 3D Gimbal Orbit Rings (3 rings on different axes)
      const ringConfigs = [
        { radius: 135, rotX: baseRotX + Math.PI / 4, rotY: baseRotY * 0.8, color: 'rgba(163, 184, 121, 0.35)', dash: [6, 8] },
        { radius: 155, rotX: baseRotX - Math.PI / 6, rotY: -baseRotY * 0.6, color: 'rgba(240, 178, 71, 0.45)', dash: [4, 12] },
        { radius: 175, rotX: baseRotX + Math.PI / 2.5, rotY: baseRotY * 1.1, color: 'rgba(74, 222, 128, 0.4)', dash: [2, 6] }
      ];

      for (const ring of ringConfigs) {
        ctx.save();
        ctx.strokeStyle = ring.color;
        ctx.lineWidth = 1.2;
        ctx.setLineDash(ring.dash);
        ctx.beginPath();

        const segments = 48;
        for (let i = 0; i <= segments; i++) {
          const theta = (i / segments) * Math.PI * 2;
          const rx = Math.cos(theta) * ring.radius;
          const ry = Math.sin(theta) * ring.radius;
          const proj = project(rx, ry, 0, ring.rotX, ring.rotY);
          if (i === 0) ctx.moveTo(proj.x, proj.y);
          else ctx.lineTo(proj.x, proj.y);
        }
        ctx.stroke();
        ctx.restore();
      }

      // Project 3D Core Vertices
      const projectedCore = coreVertices.map(([x, y, z]) => {
        // Subtle rhythmic heartbeat pulse
        const pulse = 1 + Math.sin(time * 3) * 0.04;
        return project(x * pulse, y * pulse, z * pulse, baseRotX, baseRotY);
      });

      // Draw Wireframe Edges with depth glow
      for (const [i, j] of edges) {
        const p1 = projectedCore[i];
        const p2 = projectedCore[j];

        // Depth cueing
        const avgZ = (p1.z + p2.z) / 2;
        const normZ = (avgZ + 100) / 200; // 0 (far) to 1 (near)
        const alpha = Math.max(0.15, Math.min(0.85, 0.3 + normZ * 0.55));

        ctx.beginPath();
        ctx.moveTo(p1.x, p1.y);
        ctx.lineTo(p2.x, p2.y);
        ctx.strokeStyle = avgZ > 0 ? `rgba(74, 222, 128, ${alpha})` : `rgba(163, 184, 121, ${alpha * 0.7})`;
        ctx.lineWidth = avgZ > 0 ? 1.6 : 1.0;
        ctx.stroke();
      }

      // Draw Glowing Core Nodes (Vertices)
      for (const p of projectedCore) {
        const normZ = (p.z + 100) / 200;
        const radius = Math.max(2, (3.2 * p.scale));
        const alpha = Math.max(0.2, Math.min(1.0, 0.4 + normZ * 0.6));

        // Outer glow
        const glowGrad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, radius * 3.5);
        glowGrad.addColorStop(0, `rgba(74, 222, 128, ${alpha})`);
        glowGrad.addColorStop(0.5, `rgba(163, 184, 121, ${alpha * 0.4})`);
        glowGrad.addColorStop(1, 'rgba(163, 184, 121, 0)');

        ctx.fillStyle = glowGrad;
        ctx.beginPath();
        ctx.arc(p.x, p.y, radius * 3.5, 0, Math.PI * 2);
        ctx.fill();

        // Core dot
        ctx.fillStyle = '#FFFFFF';
        ctx.beginPath();
        ctx.arc(p.x, p.y, radius * 0.7, 0, Math.PI * 2);
        ctx.fill();
      }

      // Inner glowing energy sphere
      const corePulse = 24 + Math.sin(time * 4) * 3;
      const coreProj = project(0, 0, 0, baseRotX, baseRotY);
      const innerGrad = ctx.createRadialGradient(coreProj.x, coreProj.y, 2, coreProj.x, coreProj.y, corePulse * coreProj.scale);
      innerGrad.addColorStop(0, 'rgba(240, 178, 71, 0.9)');
      innerGrad.addColorStop(0.4, 'rgba(163, 184, 121, 0.45)');
      innerGrad.addColorStop(1, 'rgba(74, 222, 128, 0)');

      ctx.fillStyle = innerGrad;
      ctx.beginPath();
      ctx.arc(coreProj.x, coreProj.y, corePulse * coreProj.scale, 0, Math.PI * 2);
      ctx.fill();

      // Scanning Laser Line through center
      const scanY = centerY + Math.sin(time * 2) * 110;
      ctx.save();
      ctx.strokeStyle = 'rgba(74, 222, 128, 0.65)';
      ctx.lineWidth = 1;
      ctx.beginPath();
      ctx.moveTo(centerX - 130, scanY);
      ctx.lineTo(centerX + 130, scanY);
      ctx.stroke();

      // Scanline glow
      const scanGrad = ctx.createLinearGradient(centerX, scanY - 6, centerX, scanY + 6);
      scanGrad.addColorStop(0, 'rgba(74, 222, 128, 0)');
      scanGrad.addColorStop(0.5, 'rgba(74, 222, 128, 0.25)');
      scanGrad.addColorStop(1, 'rgba(74, 222, 128, 0)');
      ctx.fillStyle = scanGrad;
      ctx.fillRect(centerX - 130, scanY - 6, 260, 12);
      ctx.restore();

      animationFrameId = requestAnimationFrame(render);
    };

    render();

    return () => {
      cancelAnimationFrame(animationFrameId);
      window.removeEventListener('resize', handleResize);
      window.removeEventListener('mousemove', handleMouseMove);
    };
  }, []);

  return (
    <div className={`tactical-3d-hologram-wrap ${className || ''}`} style={{ position: 'relative', width: '100%', height: '100%', minHeight: '340px' }}>
      <canvas
        ref={canvasRef}
        style={{
          width: '100%',
          height: '100%',
          display: 'block',
          filter: 'drop-shadow(0 0 24px rgba(74, 222, 128, 0.28))',
        }}
      />
    </div>
  );
};
