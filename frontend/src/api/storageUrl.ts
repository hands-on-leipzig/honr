/**
 * Base URL for backend (storage uploads, etc.). Used when path is not under images/.
 * Prefers VITE_FILES_BASE_URL (deploy); falls back to VITE_API_URL origin.
 */
export function getStorageBaseUrl(): string {
  const filesBase = import.meta.env.VITE_FILES_BASE_URL
  if (filesBase && typeof filesBase === 'string' && filesBase.trim()) {
    return filesBase.trim().replace(/\/+$/, '')
  }
  const apiUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'
  return apiUrl.replace(/\/api\/?$/, '')
}

/**
 * Full URL for a logo/storage path (e.g. logo_path from API). Returns '' if path is empty.
 * Images under images/ use origin-relative URL; Vite dev server proxies /images to backend.
 */
export function getStorageUrl(path: string | null | undefined): string {
  if (!path || typeof path !== 'string' || !path.trim()) return ''
  const normalized = path.startsWith('/') ? path.slice(1) : path
  if (normalized.startsWith('images/')) {
    return `/${normalized}`
  }
  return `${getStorageBaseUrl()}/storage/${normalized}`
}
