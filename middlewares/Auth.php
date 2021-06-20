<?php
require __DIR__ . '/../classes/JwtHandler.php';
class Auth extends JwtHandler
{

    protected $db;
    protected $headers;
    protected $token;
    public function __construct($db, $headers)
    {
        parent::__construct();
        $this->db = $db;
        $this->headers = $headers;
    }

    public function isAuth()
    {
        $data = $this->extract();
        if ($data == null) :
            return false;
        endif;

        if (isset($data['auth']) && isset($data['data']->user_id) && $data['auth']) :
            $user = $this->fetchUser($data['data']->user_id);

            $fetch_user_by_id = "SELECT `user_id` FROM `access_tokens` WHERE `user_id`=:id";
            $query_stmt = $this->db->prepare($fetch_user_by_id);
            $query_stmt->bindValue(':id', $data['data']->user_id, PDO::PARAM_INT);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) :
                return $user;
            else :
                return false;
            endif;


        else :
            return false;

        endif; // End of isset($this->token[1]) && !empty(trim($this->token[1]))
    }

    protected function fetchUser($user_id)
    {
        try {
            $fetch_user_by_id = "SELECT `name`,`username` FROM `users` WHERE `id`=:id";
            $query_stmt = $this->db->prepare($fetch_user_by_id);
            $query_stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
            $query_stmt->execute();

            if ($query_stmt->rowCount()) :
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                return [
                    'success' => 1,
                    'status' => 200,
                    'user' => $row
                ];
            else :
                return null;
            endif;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function logout()
    {
        $data = $this->extract();
        if ($data == null) :
            return $data;
        endif;

        if (isset($data['auth']) && isset($data['data']->user_id) && $data['auth']) :
            $user_id = $data['data']->user_id;
            try {
                $delete_access_user_by_id = "DELETE FROM `access_tokens` WHERE `user_id`=:id";
                $query_stmt = $this->db->prepare($delete_access_user_by_id);
                $query_stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
                $query_stmt->execute();

                return Message::output(1, 200, 'You are logged out!!');
            } catch (PDOException $e) {
                return null;
            }

        else :
            return null;

        endif; // End of isset($this->token[1]) && !empty(trim($this->token[1]))
    }

    private function extract()
    {
        if (array_key_exists('Authorization', $this->headers) && !empty(trim($this->headers['Authorization']))) :
            $this->token = explode(" ", trim($this->headers['Authorization']));
            if (isset($this->token[1]) && !empty(trim($this->token[1]))) :

                $data = $this->_jwt_decode_data($this->token[1]);

                return $data;
            else :
                return false;

            endif; // End of isset($this->token[1]) && !empty(trim($this->token[1]))

        else :
            return false;

        endif;
    }
}
