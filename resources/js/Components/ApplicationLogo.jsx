export default function ApplicationLogo(props) {
    return (
        <img
            {...props}
            src="/images/logohvk.webp"
            alt="Logo HVK"
            className={props.className || "h-9 w-auto"}
        />
    );
}
