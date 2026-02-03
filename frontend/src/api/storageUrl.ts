/**
 * Base URL for backend storage (logos, uploads). Use this for img src etc.
 * Prefers VITE_FILES_BASE_URL (set in deploy to app_url); falls back to deriving from VITE_API_URL.
 */
export function getStorageBaseUrl(): string {
  const filesBase = import.meta.env.VITE_FILES_BASE_URL
  if (filesBase && typeof filesBase === 'string' && filesBase.trim()) {
    return filesBase.trim().replace(/\/+$/, '')
  }
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return apiUrl.replace(/\/api\/?$/, '')
}

/** Full URL for a logo/storage path (e.g. logo_path from API). Returns '' if path is empty. */
export function getStorageUrl(path: string | null | undefined): string {
  if (!path || typeof path !== 'string' || !path.trim()) return ''
  const base = getStorageBaseUrl()
  const normalized = path.startsWith('/') ? path.slice(1) : path
  // Program/season logos live in public/images/logos/ (in repo); role logos in storage/
  if (normalized.startsWith('images/')) {
    return `${base}/${normalized}`
  }
  return `${base}/storage/${normalized}`
}
