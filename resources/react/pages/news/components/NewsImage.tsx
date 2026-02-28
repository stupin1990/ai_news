import { useEffect, useState } from "react";

type ImageStatus = 'loading' | 'success' | 'error';

interface NewsImageProps {
    src: string | null;
    alt: string;
}


export function NewsImage({ src, alt }: NewsImageProps) {
    const [status, setStatus] = useState<ImageStatus>(() => {
        if (!src) return 'error';
        return 'loading';
    });

    useEffect(() => {
        if (!src) return;

        const image = new Image();

        const onLoad = () => {
            setStatus('success');
        };
        const onError = () => {
            setStatus('error');
        };

        image.addEventListener('load', onLoad);
        image.addEventListener('error', onError);
        image.src = src;

        return () => {
            image.removeEventListener('load', onLoad);
            image.removeEventListener('error', onError);
        };
    }, [src]);

    if (status !== 'success' || !src) {
        return <span className="text-xs text-white">No image</span>;
    }

    return (
        <img
            src={src}
            alt={alt}
            className="h-full w-full object-contain"
            decoding="async"
            loading="lazy"
        />
    );
}