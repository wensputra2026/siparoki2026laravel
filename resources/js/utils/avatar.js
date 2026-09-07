/**
 * Utility for resolving avatar images based on gender and age / birth date.
 * Default image assets from /public/images/:
 * - Anak-anak (< 13 thn): /images/anak kecil laki-laki.jpg | /images/anak kecil perempuan.jpg
 * - Remaja / Muda (13 - 24 thn): /images/laki-laki muda.jpg | /images/perempuan muda.jpg
 * - Dewasa / Umum (>= 25 thn / default): /images/laki-laki.jpg | /images/perempuan.jpg
 */

export function isFemaleGender(gender) {
    if (!gender) return false;
    const g = String(gender).trim().toLowerCase();
    return g === 'p' || g === 'perempuan' || g === 'wanita' || g === 'female' || g === 'f';
}

export function calculateAge(birthDate) {
    if (!birthDate) return null;
    if (typeof birthDate === 'number' && !isNaN(birthDate)) return birthDate;
    try {
        let y, m, d;
        if (typeof birthDate === 'string') {
            const cleanStr = birthDate.split('T')[0];
            const parts = cleanStr.split('-');
            if (parts.length >= 3) {
                y = parseInt(parts[0], 10);
                m = parseInt(parts[1], 10) - 1;
                d = parseInt(parts[2], 10);
            }
        }
        const bDate = (y !== undefined && !isNaN(y)) ? new Date(y, m, d) : new Date(birthDate);
        if (isNaN(bDate.getTime())) return null;

        const today = new Date();
        let age = today.getFullYear() - bDate.getFullYear();
        const monthDiff = today.getMonth() - bDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < bDate.getDate())) {
            age--;
        }
        return age >= 0 ? age : null;
    } catch {
        return null;
    }
}

/**
 * Returns default avatar image URL based on gender and age/birth date.
 */
export function getDefaultAvatar(gender, birthDateOrAge = null) {
    const isFemale = isFemaleGender(gender);
    let age = null;

    if (birthDateOrAge !== null && birthDateOrAge !== undefined && birthDateOrAge !== '') {
        if (typeof birthDateOrAge === 'number') {
            age = birthDateOrAge;
        } else {
            age = calculateAge(birthDateOrAge);
        }
    }

    if (age !== null && age !== undefined && !isNaN(age)) {
        if (age < 13) {
            return isFemale ? '/images/anak kecil perempuan.jpg' : '/images/anak kecil laki-laki.jpg';
        } else if (age <= 24) {
            return isFemale ? '/images/perempuan muda.jpg' : '/images/laki-laki muda.jpg';
        }
    }

    return isFemale ? '/images/perempuan.jpg' : '/images/laki-laki.jpg';
}

/**
 * Resolves avatar image for an entity (Umat, Jiwa, KK, etc.).
 * If item has a custom uploaded photo, returns formatted URL.
 * Otherwise returns appropriate default avatar based on gender and age.
 */
export function resolveAvatarUrl(item, photoField = 'foto') {
    if (!item) return '/images/laki-laki.jpg';

    const photo = item[photoField] || item.foto || item.gambar;
    if (photo && typeof photo === 'string') {
        const trimmed = photo.trim();
        if (trimmed !== '' && trimmed !== 'null' && trimmed !== 'undefined' && trimmed !== '—') {
            if (trimmed.startsWith('http://') || trimmed.startsWith('https://') || trimmed.startsWith('data:')) {
                return trimmed;
            }
            return '/' + trimmed.replace(/^\/+/, '');
        }
    }

    const gender = item.jenis_kelamin || item.gender || item.sex;
    const ageOrBirthDate = item.usia ?? item.umur ?? item.tanggal_lahir ?? item.tgl_lahir ?? item.birth_date;

    return getDefaultAvatar(gender, ageOrBirthDate);
}
