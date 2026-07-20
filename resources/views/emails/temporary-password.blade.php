<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Your UCC LabTech Account</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f3f4f6;
    font-family: Arial, Helvetica, sans-serif;
    color: #1f2937;
">
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="background-color: #f3f4f6; padding: 30px 15px;"
    >
        <tr>
            <td align="center">
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        max-width: 600px;
                        background-color: #ffffff;
                        border-radius: 14px;
                        overflow: hidden;
                    "
                >
                    <tr>
                        <td
                            style="
                                background-color: #166534;
                                padding: 28px;
                                color: #ffffff;
                                text-align: center;
                            "
                        >
                            <h1
                                style="
                                    margin: 0;
                                    font-size: 22px;
                                    line-height: 1.4;
                                "
                            >
                                University of Caloocan City
                            </h1>

                            <p
                                style="
                                    margin: 8px 0 0;
                                    color: #dcfce7;
                                    font-size: 14px;
                                "
                            >
                                LabTech Borrowing Management System
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 30px;">
                            <h2 style="margin-top: 0;">
                                Welcome, {{ $user->first_name }}!
                            </h2>

                            <p style="line-height: 1.7;">
                                Your UCC LabTech Borrowing Management System
                                account has been created successfully.
                            </p>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    margin: 24px 0;
                                    background-color: #f0fdf4;
                                    border: 1px solid #bbf7d0;
                                    border-radius: 10px;
                                "
                            >
                                <tr>
                                    <td style="padding: 20px;">
                                        <p
                                            style="
                                                margin: 0 0 8px;
                                                font-size: 13px;
                                                color: #4b5563;
                                            "
                                        >
                                            Registered email
                                        </p>

                                        <p
                                            style="
                                                margin: 0 0 20px;
                                                font-weight: bold;
                                            "
                                        >
                                            {{ $user->email }}
                                        </p>

                                        <p
                                            style="
                                                margin: 0 0 8px;
                                                font-size: 13px;
                                                color: #4b5563;
                                            "
                                        >
                                            Temporary password
                                        </p>

                                        <p
                                            style="
                                                margin: 0;
                                                font-family: Consolas, monospace;
                                                font-size: 22px;
                                                font-weight: bold;
                                                letter-spacing: 2px;
                                                color: #166534;
                                            "
                                        >
                                            {{ $temporaryPassword }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="line-height: 1.7;">
                                For security, you will be required to create
                                a new password immediately after signing in.
                            </p>

                            <p style="line-height: 1.7;">
                                Do not share this temporary password with
                                anyone. If you did not request this account,
                                please contact the UCC laboratory administrator.
                            </p>

                            <div style="margin-top: 28px; text-align: center;">
                                <a
                                    href="{{ route('login') }}"
                                    style="
                                        display: inline-block;
                                        background-color: #15803d;
                                        color: #ffffff;
                                        text-decoration: none;
                                        padding: 12px 24px;
                                        border-radius: 8px;
                                        font-weight: bold;
                                    "
                                >
                                    Sign In to Your Account
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                                padding: 20px 30px;
                                background-color: #f9fafb;
                                color: #6b7280;
                                font-size: 12px;
                                text-align: center;
                            "
                        >
                            © {{ date('Y') }} University of Caloocan City
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>