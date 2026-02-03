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
  const normalized = path.startsWith('/') ? path.slice(1) : path
  // Program/season/role logos live in backend public/images/logos/ (in repo).
  // In dev the frontend runs on a different port (e.g. 5174) so /images/... would hit Vite, not Laravel.
  // Use backend base URL in dev; in production same-origin so origin-relative /images/... works.
  if (normalized.startsWith('images/')) {
    if (import.meta.env.DEV) {
      return `${getStorageBaseUrl()}/${normalized}`
    }
    return `/${normalized}`
  }
  const base = getStorageBaseUrl()
  return `${base}/storage/${normalized}`
}
