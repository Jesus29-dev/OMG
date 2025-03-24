import numpy as np
import matplotlib.pyplot as plt
from matplotlib.patches import Polygon

# Función a integrar
def f(x):
    return x**2 * np.exp(-x)

# Integración por el método del trapecio
def regla_trapecio(f, a, b, n):
    h = (b - a) / n
    x = np.linspace(a, b, n+1)
    y = f(x)
    
    integral = h * (0.5 * y[0] + 0.5 * y[n] + np.sum(y[1:n]))
    
    return integral, x, y

# Integración por el método de Simpson
def regla_simpson(f, a, b, n):
    if n % 2 != 0:
        n += 1  # Asegurar que n sea par
    
    h = (b - a) / n
    x = np.linspace(a, b, n+1)
    y = f(x)
    
    integral = h/3 * (y[0] + y[n] + 4*np.sum(y[1:n:2]) + 2*np.sum(y[2:n-1:2]))
    
    return integral, x, y

# Integración por el método del punto medio
def regla_punto_medio(f, a, b, n):
    h = (b - a) / n
    x_medio = np.linspace(a + h/2, b - h/2, n)
    
    integral = h * np.sum(f(x_medio))
    
    # Para graficar
    x = np.linspace(a, b, n+1)
    y = f(x)
    
    return integral, x, y, x_medio, f(x_medio)

# Parámetros
a = 0  # Límite inferior
b = 4  # Límite superior
n_trapecio = 8
n_simpson = 8
n_punto_medio = 8

# Calcular integrales para graficar
integral_trapecio, x_trapecio, y_trapecio = regla_trapecio(f, a, b, n_trapecio)
integral_simpson, x_simpson, y_simpson = regla_simpson(f, a, b, n_simpson)
integral_punto_medio, x_pm, y_pm, x_medio, y_medio = regla_punto_medio(f, a, b, n_punto_medio)

# Preparar gráfico
fig, axes = plt.subplots(3, 1, figsize=(12, 15))

# Función para graficar
x_plot = np.linspace(a, b, 1000)
y_plot = f(x_plot)

# 1. Método del Trapecio
ax = axes[0]
ax.plot(x_plot, y_plot, 'b-', linewidth=1.5, label='f(x) = x²e^(-x)')
ax.plot(x_trapecio, y_trapecio, 'ro', markersize=4)

# Dibujar trapecios
for i in range(len(x_trapecio)-1):
    verts = [(x_trapecio[i], 0), (x_trapecio[i], y_trapecio[i]), (x_trapecio[i+1], y_trapecio[i+1]), (x_trapecio[i+1], 0)]
    poly = Polygon(verts, facecolor='0.8', edgecolor='0.5', alpha=0.5)
    ax.add_patch(poly)

ax.set_xlabel('x')
ax.set_ylabel('f(x)')
ax.set_title(f'Integración por Regla del Trapecio (n={n_trapecio})')
ax.grid(True)
ax.legend()
ax.text(3, 0.5, f'Integral ≈ {integral_trapecio:.6f}')

# 2. Método de Simpson
ax = axes[1]
ax.plot(x_plot, y_plot, 'b-', linewidth=1.5, label='f(x) = x²e^(-x)')
ax.plot(x_simpson, y_simpson, 'ro', markersize=4)

# Dibujar parábolas para Simpson (simplificado)
for i in range(0, len(x_simpson)-1, 2):
    if i+2 < len(x_simpson):
        # Puntos para la parábola
        x_parabola = np.linspace(x_simpson[i], x_simpson[i+2], 100)
        # Ajustar parábola por los tres puntos
        coef = np.polyfit([x_simpson[i], x_simpson[i+1], x_simpson[i+2]], 
                          [y_simpson[i], y_simpson[i+1], y_simpson[i+2]], 2)
        y_parabola = np.polyval(coef, x_parabola)
        
        # Dibujar polígono
        x_poly = np.append(x_parabola, [x_simpson[i+2], x_simpson[i]])
        y_poly = np.append(y_parabola, [0, 0])
        poly = Polygon(list(zip(x_poly, y_poly)), facecolor='0.8', edgecolor='0.5', alpha=0.5)
        ax.add_patch(poly)
        
        # Dibujar parábola
        ax.plot(x_parabola, y_parabola, 'g-', linewidth=1, alpha=0.6)

ax.set_xlabel('x')
ax.set_ylabel('f(x)')
ax.set_title(f'Integración por Regla de Simpson (n={n_simpson})')
ax.grid(True)
ax.legend()
ax.text(3, 0.5, f'Integral ≈ {integral_simpson:.6f}')

# 3. Método del Punto Medio
ax = axes[2]
ax.plot(x_plot, y_plot, 'b-', linewidth=1.5, label='f(x) = x²e^(-x)')
ax.plot(x_pm, y_pm, 'ro', markersize=4)
ax.plot(x_medio, y_medio, 'gx', markersize=6, label='Puntos medios')

# Dibujar rectángulos
for i in range(len(x_pm)-1):
    # Punto medio
    x_mid = (x_pm[i] + x_pm[i+1]) / 2
    y_mid = f(x_mid)
    
    # Rectángulo
    rect = plt.Rectangle((x_pm[i], 0), x_pm[i+1]-x_pm[i], y_mid, 
                         facecolor='0.8', edgecolor='0.5', alpha=0.5)
    ax.add_patch(rect)

ax.set_xlabel('x')
ax.set_ylabel('f(x)')
ax.set_title(f'Integración por Regla del Punto Medio (n={n_punto_medio})')
ax.grid(True)
ax.legend()
ax.text(3, 0.5, f'Integral ≈ {integral_punto_medio:.6f}')

plt.tight_layout()
plt.show()